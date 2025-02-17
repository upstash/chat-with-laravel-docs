<?php

use App\Splitters\LaravelMarkdownSplitter;

use function PHPUnit\Framework\assertCount;
use function PHPUnit\Framework\assertSame;

test('it can do something', function () {
    $splitter = new LaravelMarkdownSplitter;

    $result = $splitter->split(<<<'MD'
    # Title
    Paragraph 1
    ## Section
    Paragraph 2
    ### Sub Section
    Paragraph 3
    MD);

    assertCount(3, $result);
    assertSame(<<<'MD'
    # Title
    Paragraph 1
    MD, $result[0]->getContent());

    assertSame(<<<'MD'
    # Title
    ## Section
    Paragraph 2
    MD, $result[1]->getContent());

    assertSame(<<<'MD'
    # Title
    ## Section
    ### Sub Section
    Paragraph 3
    MD, $result[2]->getContent());
});

test('it can handle multiple similar headings', function () {
    $splitter = new LaravelMarkdownSplitter;

    $result = $splitter->split(<<<'MD'
    # Title
    Paragraph 1
    ## Section 1
    Paragraph 2
    ## Section 2
    Paragraph 3
    MD);

    assertCount(3, $result);
    assertSame(<<<'MD'
    # Title
    Paragraph 1
    MD, $result[0]->getContent());

    assertSame(<<<'MD'
    # Title
    ## Section 1
    Paragraph 2
    MD, $result[1]->getContent());

    assertSame(<<<'MD'
    # Title
    ## Section 2
    Paragraph 3
    MD, $result[2]->getContent());
});

test('it can handle section titles and clean them', function () {
    $splitter = new LaravelMarkdownSplitter;

    $result = $splitter->split(<<<'MD'
    # Title
    Paragraph 1
    ## Section
    Paragraph 2
    #### Section 6
    Paragraph 3
    ### Sub Section
    Paragraph 4
    MD);

    assertCount(4, $result);

    assertSame(<<<'MD'
    # Title
    Paragraph 1
    MD, $result[0]->getContent());

    assertSame(<<<'MD'
    # Title
    ## Section
    Paragraph 2
    MD, $result[1]->getContent());

    assertSame(<<<'MD'
    # Title
    ## Section
    #### Section 6
    Paragraph 3
    MD, $result[2]->getContent());

    assertSame(<<<'MD'
    # Title
    ## Section
    ### Sub Section
    Paragraph 4
    MD, $result[3]->getContent());
});
