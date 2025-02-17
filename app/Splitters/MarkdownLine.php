<?php

namespace App\Splitters;

use Stringable;

class MarkdownLine implements Stringable
{
    public function __construct(private string $content) {}

    public function isTitle(): bool
    {
        return preg_match('/^#{1,6}/', $this);
    }

    public function getLinkName(): ?string
    {
        preg_match('/<a[^>]*name=["\']([^"\']+)["\'][^>]*>/', $this, $matches);
        if (count($matches) > 0) {
            return $matches[1];
        }

        return null;
    }

    public function isLinkTag(): bool
    {
        return $this->getLinkName() !== null;
    }

    public function __toString(): string
    {
        return $this->content;
    }
}
