<?php

namespace App\Console\Commands;

use App\Splitters\Document;
use App\Splitters\LaravelMarkdownSplitter;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Str;
use SplFileInfo;
use Upstash\Vector\DataUpsert;
use Upstash\Vector\Laravel\Facades\Vector;

class IngestDocumentation extends Command
{
    /**
     * The name and signature of the console command
     *
     * @var string
     */
    protected $signature = 'app:ingest:documentation';

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
        $this->version = '12';
        $path = storage_path('/docs');

        File::deleteDirectory($path);

        Process::run('git clone https://github.com/laravel/docs.git '.$path);
        File::deleteDirectory($path.'/.git');

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
            ->map(fn (Document $document) => new DataUpsert(
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
}
