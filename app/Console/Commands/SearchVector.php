<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use OpenAI\Laravel\Facades\OpenAI;
use Upstash\Vector\DataQuery;
use Upstash\Vector\Enums\QueryMode;
use Upstash\Vector\Laravel\Facades\Vector;
use Upstash\Vector\VectorMatch;

use function Laravel\Prompts\spin;
use function Laravel\Prompts\text;

class SearchVector extends Command
{
    protected array $messages = [];

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:search-vector';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->messages[] = [
            'role' => 'system',
            'content' => view('prompts.system', ['version' => '11.x'])->render(),
        ];

        while (true) {
            $this->info('Messages: '.count($this->messages));
            $this->promptSearch();
        }
    }

    private function embeddings(): void
    {
        $results = OpenAI::embeddings()->create([
            'model' => 'text-embedding-ada-002',
            'input' => 'The food was delicious and the waiter...',
        ]);

        dd($results->embeddings);
    }

    private function promptSearch(): void
    {
        $question = text('What is the query?');

        $results = Vector::queryData(new DataQuery(
            data: $question,
            topK: 8,
            includeMetadata: true,
            includeData: true,
            queryMode: QueryMode::DENSE,
        ));

        $context = collect($results)
            ->map(fn (VectorMatch $result) => $result->data)
            ->implode("\n---\n");

        $this->messages[] = [
            'role' => 'user',
            'content' => view('prompts.question', ['question' => $question, 'context' => $context])->render(),
        ];

        $result = spin(fn () => OpenAI::chat()->create([
            'model' => 'gpt-4o-mini',
            'messages' => $this->messages,
        ]));

        foreach ($result->choices as $choice) {
            $this->messages[] = [
                'role' => 'assistant',
                'content' => $choice->message->content,
            ];

            $this->info($choice->message->content);

            if ($choice->finishReason === 'stop') {
                break;
            }
        }
    }
}
