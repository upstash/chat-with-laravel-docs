<?php

namespace App\Http\Controllers;

use App\Actions\Rerank;
use Illuminate\Http\Request;
use Upstash\Vector\DataQuery;
use Upstash\Vector\Enums\QueryMode;
use Upstash\Vector\Laravel\Facades\Vector;

class QueryController extends Controller
{
    public function index(Request $request, Rerank $rerank)
    {
        $request->validate([
            'q' => 'required|string',
        ]);

        $question = $request->get('q');

        $result = Vector::queryData(new DataQuery(
            data: $question,
            topK: 15,
            includeMetadata: true,
            includeData: true,
            queryMode: QueryMode::DENSE,
        ));

        $result = $rerank->run($question, $result);

        $bestMatch = $result->offsetGet(0);

        $url = $this->findUrl($bestMatch->metadata['sources']);

        return response()->redirectTo($url);
    }

    private function findUrl(array $sources): string
    {
        return collect($sources)
            ->sortBy(fn ($source) => strlen($source))
            ->reverse()
            ->first();
    }
}
