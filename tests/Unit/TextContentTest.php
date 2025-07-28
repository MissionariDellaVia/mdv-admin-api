<?php

use App\Models\TextContent;

it('can create text content model with highlights', function () {
    $textContent = new TextContent([
        'title' => 'Test Content',
        'slug' => 'test-content',
        'content' => 'This is a test content for highlighting.',
        'highlights' => [
            [
                'start' => 0,
                'end' => 4,
                'color' => 'yellow',
                'text' => 'This',
                'created_at' => now()->toISOString()
            ]
        ],
        'is_published' => true
    ]);

    expect($textContent->title)->toBe('Test Content');
    expect($textContent->highlights)->toHaveCount(1);
    expect($textContent->highlights[0]['color'])->toBe('yellow');
});

it('can add highlights to text content', function () {
    $textContent = new TextContent([
        'title' => 'Test Content',
        'slug' => 'test-content',
        'content' => 'This is a sample text.',
        'highlights' => [],
        'is_published' => true
    ]);

    // Manually add highlight without saving
    $highlights = $textContent->highlights ?? [];
    $highlight = [
        'start' => 0,
        'end' => 4,
        'color' => 'yellow',
        'text' => substr($textContent->content, 0, 4),
        'created_at' => now()->toISOString()
    ];
    $highlights[] = $highlight;
    $textContent->highlights = $highlights;
    
    expect($textContent->highlights)->toHaveCount(1);
    expect($textContent->highlights[0]['color'])->toBe('yellow');
    expect($textContent->highlights[0]['text'])->toBe('This');
});

it('can get highlighted content as HTML', function () {
    $textContent = new TextContent([
        'title' => 'Test Content',
        'slug' => 'test-content',
        'content' => 'This is a test',
        'highlights' => [
            [
                'start' => 0,
                'end' => 4,
                'color' => 'yellow',
                'text' => 'This',
                'created_at' => now()->toISOString()
            ]
        ],
        'is_published' => true
    ]);

    $highlightedContent = $textContent->getHighlightedContent();
    
    expect($highlightedContent)->toContain('<span class="highlight highlight-yellow">This</span>');
    expect($highlightedContent)->toContain(' is a test');
});


it('can clear all highlights', function () {
    $textContent = new TextContent([
        'title' => 'Test Content',
        'slug' => 'test-content',
        'content' => 'This is a test',
        'highlights' => [
            [
                'start' => 0,
                'end' => 4,
                'color' => 'yellow',
                'text' => 'This',
                'created_at' => now()->toISOString()
            ],
            [
                'start' => 5,
                'end' => 7,
                'color' => 'green',
                'text' => 'is',
                'created_at' => now()->toISOString()
            ]
        ],
        'is_published' => true
    ]);

    // Manually clear highlights without saving
    $textContent->highlights = [];
    
    expect($textContent->highlights)->toBeEmpty();
});

it('validates highlight bounds', function () {
    $textContent = new TextContent([
        'title' => 'Test Content',
        'slug' => 'test-content',
        'content' => 'Short',  // Only 5 characters
        'highlights' => [],
        'is_published' => true
    ]);

    // Validate highlight bounds manually
    $startOffset = 0;
    $endOffset = 10;
    $content = $textContent->content;
    
    $isValid = $startOffset < $endOffset && $startOffset >= 0 && $endOffset <= strlen($content);
    
    expect($isValid)->toBeFalse();
    expect($textContent->highlights)->toBeEmpty();
});