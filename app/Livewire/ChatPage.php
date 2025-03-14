<?php

namespace App\Livewire;

use App\Actions\Rerank;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Component;
use OpenAI\Laravel\Facades\OpenAI;
use Upstash\Vector\DataQuery;
use Upstash\Vector\Enums\QueryMode;
use Upstash\Vector\Laravel\Facades\Vector;
use Upstash\Vector\VectorMatch;

class ChatPage extends Component
{
    public string $question = '';

    public bool $isChatLoading = false;

    public bool $useReranker = false;

    public array $chat = [
        [
            'role' => 'assistant',
            'content' => 'Hello! How can I help you today?',
        ],
    ];

    public array $context = [];

    public function askQuestion()
    {
        if ($this->isRateLimited()) {
            return;
        }

        // reset and append new message to chat
        $this->chat = [
            [
                'role' => 'assistant',
                'content' => 'Hello! How can I help you today?',
            ],
            [
                'role' => 'user',
                'content' => $this->question,
            ],
        ];

        // loopback from JS
        $this->js(sprintf('$wire.processQuestion("%s")', $this->question));

        // reset state
        $this->question = '';
        $this->context = [];
        $this->isChatLoading = true;
    }

    private function isRateLimited(): bool
    {
        if (! config('chat.rate_limit_enabled')) {
            return false;
        }

        $key = sprintf('chat:%s', md5(request()->ip()));

        if (RateLimiter::tooManyAttempts($key, maxAttempts: 2)) {
            $seconds = RateLimiter::availableIn($key);
            $message = sprintf('You have been rate-limited. You may try again in %s seconds.', $seconds);
            $this->js(sprintf("alert('%s')", $message));

            return true;
        }

        RateLimiter::increment($key);

        return false;
    }

    public function processQuestion(string $question)
    {
        $results = Vector::queryData(new DataQuery(
            data: $question,
            topK: $this->useReranker ? 10 : 8,
            includeMetadata: true,
            includeData: true,
            queryMode: QueryMode::DENSE,
        ));

        if ($this->useReranker) {
            $reranker = new Rerank;
            $results = $reranker->run($question, $results);
        }

        $this->context = collect($results)
            ->take(8)
            ->map(fn (VectorMatch $result) => [
                'text' => $result->data,
                'score' => $result->score,
                'sources' => $result->metadata['sources'],
            ])
            ->toArray();

        $this->js(sprintf('$wire.generateAnswer("%s")', $question));
    }

    public function generateAnswer(string $question)
    {
        $this->isChatLoading = false;

        $context = collect($this->context)
            ->map(fn (array $item) => $item['text'])
            ->implode("\n---\n");

        $messages = [
            [
                'role' => 'system',
                'content' => view('prompts.system', ['version' => '12.x'])->render(),
            ],
            [
                'role' => 'assistant',
                'content' => 'Hello! How can I help you today?',
            ],
            [
                'role' => 'user',
                'content' => view('prompts.question', ['question' => $question, 'context' => $context])->render(),
            ],
        ];

        $stream = OpenAI::chat()->createStreamed([
            'model' => 'gpt-4o-mini',
            'messages' => $messages,
            'max_tokens' => 8000,
        ]);

        $text = '';
        $markdownRenderer = app(\Spatie\LaravelMarkdown\MarkdownRenderer::class)
            ->disableHighlighting();

        foreach ($stream as $response) {
            $text .= $response->choices[0]->delta->content;
            $this->stream(to: 'answer', content: $markdownRenderer->toHtml($text), replace: true);
        }

        $this->chat[] = [
            'role' => 'assistant',
            'content' => $markdownRenderer->highlightCode()->toHtml($text),
            'sources' => collect($this->context)
                ->map(fn (array $item) => $item['sources'])
                ->flatten()
                ->unique()
                ->take(3)
                ->toArray(),
        ];
    }

    public function render()
    {
        return view('livewire.chat-page');
    }
}
