<?php

namespace App\Console\Commands;

use App\Splitters\Document;
use App\Splitters\LaravelMarkdownSplitter;
use Illuminate\Console\Command;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use SplFileInfo;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Upstash\Vector\DataUpsert;
use Upstash\Vector\Laravel\Facades\Vector;

class IngestDocumentation extends Command
{
    /**
     * The name and signature of the console command
     * @var string
     */
    protected $signature = 'app:ingest:documentation {version=11}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    protected array $parts = [];

    private string $version;

    public function handle()
    {
        $this->version = $this->argument('version');
        $path = storage_path('/docs');

        $files = File::files($path);

        // clear the vector before indexing
        Vector::reset();

        $memory = memory_get_usage();
        foreach ($files as $fileInfo) {
            $this->ingestFile($fileInfo);
        }
        $usedMemory = (memory_get_usage() - $memory) / 1024;

        $this->info("Total used memory was $usedMemory kb");
    }

    private function getLaravelDocsUrl(string $path): string
    {
        return "https://laravel.com/docs/{$this->version}.x/$path";
    }

    private function ingestFile(SplFileInfo $fileInfo)
    {
        $fileName = $fileInfo->getBasename('.md');
        $contents = File::get($fileInfo->getPathname());

        $memory = memory_get_usage();
        $splitter = new LaravelMarkdownSplitter($this->getLaravelDocsUrl($fileName));
        $documents = $splitter->split($contents);
        $usedMemory = (memory_get_usage() - $memory) / 1024;

        $upserts = collect($documents)
            ->filter(function(Document $document) {
                return !$this->contentIsJustHeadings($document);
            })
            ->map(fn(Document $document) => new DataUpsert(
                id: Str::uuid(),
                data: $document->getContent(),
                metadata: [
                    'file' => $fileName,
                    'version' => $this->version,
                    'sources' => $document->getLinks(),
                ],
            ));

        $documentCount = $upserts->count();

        Vector::upsertDataMany($upserts->toArray());

        $this->info("File $fileName has $documentCount documents, used $usedMemory kb.");
    }

    private function contentIsJustHeadings(Document $document): bool
    {

        return collect(explode("\n", $document->getContent()))
            ->every(fn($line) => Str::startsWith($line, '#'));
    }
}
