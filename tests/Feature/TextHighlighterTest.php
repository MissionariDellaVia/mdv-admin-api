<?php

it('can access the text highlighter page', function () {
    $response = $this->get('/text-highlighter');

    $response->assertStatus(200);
    $response->assertViewIs('text-highlighter');
    $response->assertSee('Text Highlighter');
    $response->assertSee('Select text to highlight and export');
});

it('displays all required UI elements', function () {
    $response = $this->get('/text-highlighter');

    // Check for color palette buttons
    $response->assertSee('Yellow highlight');
    $response->assertSee('Green highlight');
    $response->assertSee('Blue highlight');
    $response->assertSee('Pink highlight');
    $response->assertSee('Purple highlight');
    
    // Check for action buttons
    $response->assertSee('Clear All');
    $response->assertSee('Export Text');
    $response->assertSee('Export Image');
    
    // Check for sample content
    $response->assertSee('Sample Text for Highlighting');
    $response->assertSee('Cross-Platform Compatibility');
    $response->assertSee('Features');
    
    // Check for instructions
    $response->assertSee('How to Use');
});

it('includes the correct meta tags for mobile optimization', function () {
    $response = $this->get('/text-highlighter');

    $response->assertSee('width=device-width, initial-scale=1', false);
    $response->assertSee('name="csrf-token"', false);
});

it('includes the text highlighter JavaScript module', function () {
    $response = $this->get('/text-highlighter');

    // Check that Vite assets are included (either @vite directive or compiled assets)
    $content = $response->getContent();
    $hasViteDirective = str_contains($content, '@vite');
    $hasCompiledAssets = str_contains($content, 'build/assets/app-') && str_contains($content, '.js');
    
    expect($hasViteDirective || $hasCompiledAssets)->toBeTrue();
});

it('provides a back link to home page', function () {
    $response = $this->get('/text-highlighter');

    $response->assertSee('← Back to Home');
});