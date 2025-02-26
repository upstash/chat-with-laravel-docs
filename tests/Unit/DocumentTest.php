<?php

use App\Splitters\Document;

test('document can identify if it has only titles', function () {
    $document = new Document;
    $document->pushContent('# Title');
    $document->pushContent('## Title');
    expect($document->hasOnlyTitles())->toBeTrue();

    $document->pushContent('Content');
    expect($document->hasOnlyTitles())->toBeFalse();
});
