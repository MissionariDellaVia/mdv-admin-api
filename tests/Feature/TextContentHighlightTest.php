<?php

use App\Models\TextContent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    Sanctum::actingAs($this->user);
});

it('can create text content with highlights', function () {
    $data = [
        'title' => 'Test Content',
        'content' => 'This is a test content for highlighting.',
        'highlights' => [
            [
                'start' => 5,
                'end' => 7,
                'color' => 'yellow',
                'text' => 'is',
                'created_at' => now()->toISOString()
            ]
        ],
        'is_published' => true
    ];

    $response = $this->postJson('/api/mdv/v1/content/pages', $data);

    $response->assertStatus(201);
    $response->assertJsonStructure([
        'content_id',
        'title',
        'content',
        'highlights',
        'is_published'
    ]);

    $textContent = TextContent::first();
    expect($textContent->highlights)->toHaveCount(1);
    expect($textContent->highlights[0]['color'])->toBe('yellow');
});

it('can add a highlight to existing text content', function () {
    $textContent = TextContent::factory()->create([
        'content' => 'This is a sample text for highlighting tests.',
        'highlights' => []
    ]);

    $highlightData = [
        'start' => 10,
        'end' => 16,
        'color' => 'green'
    ];

    $response = $this->postJson("/api/mdv/v1/content/pages/{$textContent->content_id}/highlights", $highlightData);

    $response->assertStatus(200);
    $response->assertJsonStructure([
        'content_id',
        'highlights'
    ]);

    $textContent->refresh();
    expect($textContent->highlights)->toHaveCount(1);
    expect($textContent->highlights[0]['color'])->toBe('green');
    expect($textContent->highlights[0]['text'])->toBe('sample');
});

it('validates highlight data when adding highlight', function () {
    $textContent = TextContent::factory()->create();

    $invalidData = [
        'start' => -1,  // Invalid start position
        'end' => 5,
        'color' => 'invalid-color'  // Invalid color
    ];

    $response = $this->postJson("/api/mdv/v1/content/pages/{$textContent->content_id}/highlights", $invalidData);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['start', 'color']);
});

it('can remove a highlight by index', function () {
    $textContent = TextContent::factory()->create([
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
        ]
    ]);

    $response = $this->deleteJson("/api/mdv/v1/content/pages/{$textContent->content_id}/highlights", [
        'index' => 0
    ]);

    $response->assertStatus(200);

    $textContent->refresh();
    expect($textContent->highlights)->toHaveCount(1);
    expect($textContent->highlights[0]['color'])->toBe('green');
});

it('can clear all highlights', function () {
    $textContent = TextContent::factory()->create([
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
        ]
    ]);

    $response = $this->deleteJson("/api/mdv/v1/content/pages/{$textContent->content_id}/highlights/clear");

    $response->assertStatus(200);

    $textContent->refresh();
    expect($textContent->highlights)->toBeEmpty();
});

it('can get highlighted content as HTML', function () {
    $textContent = TextContent::factory()->create([
        'content' => 'This is a test',
        'highlights' => [
            [
                'start' => 0,
                'end' => 4,
                'color' => 'yellow',
                'text' => 'This',
                'created_at' => now()->toISOString()
            ]
        ]
    ]);

    $response = $this->getJson("/api/mdv/v1/content/pages/{$textContent->content_id}/highlighted");

    $response->assertStatus(200);
    $response->assertJsonStructure([
        'content',
        'title',
        'highlights_count'
    ]);

    $data = $response->json();
    expect($data['highlights_count'])->toBe(1);
    expect($data['content'])->toContain('highlight-yellow');
});

it('can export highlighted text', function () {
    $textContent = TextContent::factory()->create([
        'title' => 'Test Document',
        'content' => 'This is a test document for exporting.',
        'highlights' => [
            [
                'start' => 0,
                'end' => 4,
                'color' => 'yellow',
                'text' => 'This',
                'created_at' => now()->toISOString()
            ]
        ]
    ]);

    $response = $this->getJson("/api/mdv/v1/content/pages/{$textContent->content_id}/export");

    $response->assertStatus(200);
    $response->assertJsonStructure([
        'success',
        'data' => [
            'content',
            'filename'
        ]
    ]);

    $data = $response->json('data');
    expect($data['content'])->toContain('Test Document');
    expect($data['content'])->toContain('[YELLOW] This');
    expect($data['filename'])->toContain('.txt');
});

it('can access text highlighter view for specific content', function () {
    $textContent = TextContent::factory()->create([
        'title' => 'Sample Content',
        'content' => 'This is sample content for highlighting.',
        'is_published' => true
    ]);

    $response = $this->get("/text-content/{$textContent->content_id}/highlighter");

    $response->assertStatus(200);
    $response->assertViewIs('text-highlighter');
    $response->assertViewHas('textContent', $textContent);
    $response->assertSee($textContent->title);
});

it('validates highlight offsets are within content bounds', function () {
    $textContent = TextContent::factory()->create([
        'content' => 'Short text'  // Only 10 characters
    ]);

    $invalidData = [
        'start' => 5,
        'end' => 15,  // Beyond content length
        'color' => 'yellow'
    ];

    $response = $this->postJson("/api/mdv/v1/content/pages/{$textContent->content_id}/highlights", $invalidData);

    $response->assertStatus(400);
});

it('can handle multiple highlights with different colors', function () {
    $textContent = TextContent::factory()->create([
        'content' => 'This is a wonderful and amazing text sample.'
    ]);

    // Add multiple highlights
    $highlights = [
        ['start' => 0, 'end' => 4, 'color' => 'yellow'],
        ['start' => 10, 'end' => 19, 'color' => 'green'],
        ['start' => 24, 'end' => 31, 'color' => 'blue']
    ];

    foreach ($highlights as $highlight) {
        $response = $this->postJson("/api/mdv/v1/content/pages/{$textContent->content_id}/highlights", $highlight);
        $response->assertStatus(200);
    }

    $textContent->refresh();
    expect($textContent->highlights)->toHaveCount(3);

    // Test highlighted content contains all colors
    $response = $this->getJson("/api/mdv/v1/content/pages/{$textContent->content_id}/highlighted");
    $content = $response->json('content');
    
    expect($content)->toContain('highlight-yellow');
    expect($content)->toContain('highlight-green');
    expect($content)->toContain('highlight-blue');
});