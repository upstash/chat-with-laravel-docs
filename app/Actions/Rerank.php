<?php

namespace App\Actions;

use OpenAI\Laravel\Facades\OpenAI;
use Upstash\Vector\DataQueryResult;
use Upstash\Vector\VectorMatch;

class Rerank 
{
    public function __construct()
    {
        //
    }

    public function run(string $query, DataQueryResult $result): DataQueryResult
    {
        // TODO: Implement Semantic Cache
        
        $prompt = $this->preparePrompt($query, $result);

        $keyedBy = collect($result)->keyBy(fn(VectorMatch $match) => $match->id); 

        $rerankedIds = $this->getRerankedIdsFromGpt($prompt);

        $rerankedResults = [];
        foreach ($rerankedIds as $id) {
            $rerankedResults[] = $keyedBy->get($id);
        }

        return new DataQueryResult($rerankedResults);
    }

    private function preparePrompt(string $query, DataQueryResult $result): string
    {
        $context = [];

        foreach ($result as $result) {
            $context[$result->id] = [
                'id' => $result->id,
                'content' => $result->data,
            ];
        }

        return view('prompts.rerank', [
            'query' => $query,
            'context' => json_encode($context, JSON_PRETTY_PRINT),
        ])->render();
    }

    private function getRerankedIdsFromGpt(string $prompt): array
    {
        $result = OpenAI::chat()->create([
            'model' => 'gpt-4o-mini',
            'max_tokens' => 8000,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => view('prompts.rerank_system')->render(),
                ],
                [
                    'role' => 'user',
                    'content' => $prompt,
                ],
            ],
            'response_format' => [
                'type' => 'json_schema',
                'json_schema' => [
                    'name' => 'reranked_results',
                    'schema' => [
                        'type' => 'object',
                        'properties' => [
                            'results' => [
                                'type' => 'array',
                                'items' => [
                                    'type' => 'string',
                                ],
                            ],
                        ],
                        'additionalProperties' => false,
                        'required' => ['results'],
                    ],
                    'strict' => true,
                ]
            ],
        ]);

        $json = json_decode($result->choices[0]->message->content, true);

        return $json['results'];
    }
}