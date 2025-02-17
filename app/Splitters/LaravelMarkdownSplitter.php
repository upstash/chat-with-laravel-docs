<?php

namespace App\Splitters;

class LaravelMarkdownSplitter
{
    public function __construct(private string $baseUrl = 'https://laravel.com/docs') {}

    public function split(string $contents): array
    {
        $lines = explode("\n", $contents);
        $headings = [];
        $documents = [];
        $currentDocument = null;
        $currentLink = '';

        foreach ($lines as $line) {
            $line = new MarkdownLine($line);
            if ($line->isTitle()) {
                $index = $this->getTitleIndex($line);
                $headings = array_slice($headings, 0, $index);
                $headings[$index] = $line->__toString();
                if ($currentDocument !== null) {
                    $documents[] = $currentDocument;
                }
                $currentDocument = new Document;
                $currentDocument->pushContent($this->startDocumentHeadings($headings));
                $currentDocument->pushSource($this->baseUrl);
                if (! empty($currentLink)) {
                    $currentDocument->pushSource("{$this->baseUrl}#$currentLink");
                }

                continue;
            }

            // we skip everything until headings appear
            if (count($headings) === 0) {
                continue;
            }

            // we skip link tags
            if ($line->isLinkTag()) {
                $currentLink = $line->getLinkName();

                continue;
            }

            $currentLink = '';
            if ($currentDocument) {
                $currentDocument->pushContent($line->__toString());
            }
        }

        $documents[] = $currentDocument;

        return array_values(array_filter($documents));
    }

    private function getTitleIndex(string $line): int
    {
        $count = 0;
        for ($i = 0; $i < strlen($line); $i++) {
            if ($line[$i] == '#') {
                $count++;

                continue;
            }

            break;
        }

        return $count > 0 ? $count - 1 : 0;
    }

    private function startDocumentHeadings(array $headings): string
    {
        return implode("\n", array_values($headings));
    }
}
