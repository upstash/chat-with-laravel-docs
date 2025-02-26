<?php

namespace App\Splitters;

use Stringable;

class Document implements Stringable
{
    private array $links = [];

    private string $content = '';

    public function pushContent(string $content): self
    {
        if ($this->content === '') {
            $this->content = $content;

            return $this;
        }

        $this->content = implode("\n", [
            $this->content,
            $content,
        ]);

        return $this;
    }

    public function pushSource(string $link): self
    {
        $this->links[] = $link;

        return $this;
    }

    public function __toString(): string
    {
        if (empty($this->links)) {
            return $this->content;
        }

        $linkText = implode("\n", array_map(function ($link) {
            return "- $link";
        }, $this->links));

        $linkText = "Sources:\n$linkText";

        return "{$this->content}\n\n$linkText";
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getLinks(): array
    {
        return $this->links;
    }

    public function hasOnlyTitles(): bool
    {
        $lines = explode("\n", trim($this->content));

        return count($lines) === count(array_filter($lines, fn ($line) => preg_match('/^#{1,6}/', $line)));
    }
}
