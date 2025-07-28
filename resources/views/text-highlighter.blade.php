<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Text Highlighter - {{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Styles / Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased dark:bg-black dark:text-white/50">
        <div class="bg-gray-50 text-black/50 dark:bg-black dark:text-white/50 min-h-screen">
            <div class="relative min-h-screen flex flex-col items-center justify-center selection:bg-[#FF2D20] selection:text-white">
                <div class="relative w-full max-w-4xl px-6 lg:max-w-6xl">
                    <!-- Header -->
                    <header class="py-6 text-center">
                        <h1 class="text-3xl font-bold text-black dark:text-white mb-2">Text Highlighter</h1>
                        <p class="text-gray-600 dark:text-gray-400">Select text to highlight and export</p>
                    </header>

                    <!-- Main Content -->
                    <main class="mt-6">
                        <!-- Control Panel -->
                        <div class="bg-white dark:bg-zinc-900 rounded-lg shadow-lg p-4 md:p-6 mb-6">
                            <div class="flex flex-col sm:flex-row gap-4 items-center justify-between">
                                <div class="color-palette flex flex-wrap gap-2">
                                    <!-- Highlight Colors -->
                                    <button id="highlight-yellow" class="color-button w-8 h-8 sm:w-10 sm:h-10 rounded-full bg-yellow-300 border-2 border-gray-300 hover:border-gray-500 focus:border-blue-500 transition-colors" data-color="yellow" title="Yellow highlight"></button>
                                    <button id="highlight-green" class="color-button w-8 h-8 sm:w-10 sm:h-10 rounded-full bg-green-300 border-2 border-gray-300 hover:border-gray-500 focus:border-blue-500 transition-colors" data-color="green" title="Green highlight"></button>
                                    <button id="highlight-blue" class="color-button w-8 h-8 sm:w-10 sm:h-10 rounded-full bg-blue-300 border-2 border-gray-300 hover:border-gray-500 focus:border-blue-500 transition-colors" data-color="blue" title="Blue highlight"></button>
                                    <button id="highlight-pink" class="color-button w-8 h-8 sm:w-10 sm:h-10 rounded-full bg-pink-300 border-2 border-gray-300 hover:border-gray-500 focus:border-blue-500 transition-colors" data-color="pink" title="Pink highlight"></button>
                                    <button id="highlight-purple" class="color-button w-8 h-8 sm:w-10 sm:h-10 rounded-full bg-purple-300 border-2 border-gray-300 hover:border-gray-500 focus:border-blue-500 transition-colors" data-color="purple" title="Purple highlight"></button>
                                </div>
                                
                                <div class="export-buttons flex gap-2 w-full sm:w-auto">
                                    <button id="clear-highlights" class="px-3 py-2 text-sm bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors">
                                        Clear All
                                    </button>
                                    <button id="export-text" class="px-3 py-2 text-sm bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors">
                                        Export Text
                                    </button>
                                    <button id="export-image" class="px-3 py-2 text-sm bg-green-500 text-white rounded-lg hover:bg-green-600 transition-colors">
                                        Export Image
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Text Content Area -->
                        <div class="bg-white dark:bg-zinc-900 rounded-lg shadow-lg p-8">
                            <div id="text-content" class="prose max-w-none dark:prose-invert" data-selectable="true">
                                <h2>Sample Text for Highlighting</h2>
                                <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
                                
                                <p>Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>
                                
                                <h3>Cross-Platform Compatibility</h3>
                                <p>This text highlighter is designed to work seamlessly across different platforms including iOS, Android, and desktop browsers. The interface is optimized for touch interactions while maintaining keyboard and mouse support.</p>
                                
                                <p>Select any portion of this text to see the highlighting feature in action. You can choose different colors for your highlights and export the highlighted text in various formats.</p>
                                
                                <h3>Features</h3>
                                <ul>
                                    <li>Multiple highlight colors</li>
                                    <li>Cross-platform text selection</li>
                                    <li>Export to plain text</li>
                                    <li>Export to image format</li>
                                    <li>Mobile-friendly interface</li>
                                    <li>Lightweight implementation</li>
                                </ul>
                                
                                <p>The implementation focuses on performance and user experience, ensuring that text selection and highlighting operations are smooth and responsive across all supported devices.</p>
                            </div>
                        </div>

                        <!-- Instructions -->
                        <div class="mt-6 bg-blue-50 dark:bg-blue-950 rounded-lg p-4">
                            <h3 class="text-lg font-semibold text-blue-800 dark:text-blue-200 mb-2">How to Use</h3>
                            <ol class="list-decimal list-inside text-blue-700 dark:text-blue-300 space-y-1">
                                <li>Select text by clicking and dragging or using touch gestures</li>
                                <li>Choose a highlight color from the palette above</li>
                                <li>Your selected text will be highlighted with the chosen color</li>
                                <li>Use "Export Text" to download highlighted content as plain text</li>
                                <li>Use "Export Image" to download the content as an image</li>
                                <li>Use "Clear All" to remove all highlights</li>
                            </ol>
                        </div>
                    </main>

                    <!-- Footer -->
                    <footer class="py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                        <a href="{{ url('/') }}" class="hover:text-gray-700 dark:hover:text-gray-200 transition-colors">← Back to Home</a>
                    </footer>
                </div>
            </div>
        </div>

        <!-- Hidden canvas for image export -->
        <canvas id="export-canvas" style="display: none;"></canvas>
    </body>
</html>