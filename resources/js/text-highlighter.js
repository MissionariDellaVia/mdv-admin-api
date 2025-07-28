/**
 * TextHighlighter - Cross-platform text highlighting utility
 * Supports iOS, Android, and desktop browsers with API integration
 */
class TextHighlighter {
    constructor() {
        this.selectedColor = 'yellow';
        this.highlights = new Map();
        this.highlightId = 0;
        this.isSelecting = false;
        this.touchTimeout = null;
        this.selectionTimeout = null;
        this.initialTouch = null;
        this.contentId = null;
        this.apiBaseUrl = '/api/mdv/v1/content/pages';
        
        this.init();
    }

    init() {
        this.loadContentId();
        this.bindEvents();
        this.setActiveColor(this.selectedColor);
        this.loadExistingHighlights();
    }

    loadContentId() {
        const textContent = document.getElementById('text-content');
        this.contentId = textContent?.dataset.contentId || null;
    }

    async loadExistingHighlights() {
        if (!this.contentId) return;
        
        try {
            const response = await fetch(`${this.apiBaseUrl}/${this.contentId}/highlighted`, {
                headers: {
                    'Authorization': `Bearer ${this.getAuthToken()}`,
                    'Content-Type': 'application/json',
                }
            });
            
            if (response.ok) {
                const data = await response.json();
                // The highlighted content is already rendered from the server
                console.log(`Loaded content with ${data.data.highlights_count} highlights`);
            }
        } catch (error) {
            console.warn('Could not load existing highlights:', error);
        }
    }

    getAuthToken() {
        // Try to get token from meta tag, localStorage, or return empty
        const metaToken = document.querySelector('meta[name="api-token"]');
        if (metaToken) return metaToken.getAttribute('content');
        
        return localStorage.getItem('auth_token') || '';
    }

    async makeApiRequest(endpoint, method = 'GET', data = null) {
        const options = {
            method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            }
        };

        const authToken = this.getAuthToken();
        if (authToken) {
            options.headers['Authorization'] = `Bearer ${authToken}`;
        }

        if (data) {
            options.body = JSON.stringify(data);
        }

        try {
            const response = await fetch(endpoint, options);
            const result = await response.json();
            
            if (!response.ok) {
                throw new Error(result.message || 'API request failed');
            }
            
            return result;
        } catch (error) {
            console.error('API request failed:', error);
            throw error;
        }
    }

    bindEvents() {
        // Color selection
        document.querySelectorAll('[data-color]').forEach(button => {
            button.addEventListener('click', (e) => {
                this.setActiveColor(e.target.dataset.color);
            });
        });

        // Clear highlights
        document.getElementById('clear-highlights').addEventListener('click', async () => {
            await this.clearAllHighlights();
        });

        // Export functions
        document.getElementById('export-text').addEventListener('click', async () => {
            await this.exportAsText();
        });

        document.getElementById('export-image').addEventListener('click', () => {
            this.exportAsImage();
        });

        // Text selection events
        const textContent = document.getElementById('text-content');
        
        // Mouse events (desktop)
        textContent.addEventListener('mouseup', (e) => {
            if (!this.isSelecting) {
                this.handleTextSelection(e);
            }
        });

        // Touch events (mobile) with improved handling
        textContent.addEventListener('touchstart', (e) => {
            this.isSelecting = true;
            // Clear any existing timeout
            if (this.touchTimeout) {
                clearTimeout(this.touchTimeout);
            }
            
            // Store initial touch position for gesture detection
            this.initialTouch = {
                x: e.touches[0].clientX,
                y: e.touches[0].clientY,
                time: Date.now()
            };
        });

        textContent.addEventListener('touchmove', (e) => {
            // Allow natural text selection on touch move
            // Don't prevent default to allow native selection behavior
        });

        textContent.addEventListener('touchend', (e) => {
            // Delay to allow selection to complete on mobile devices
            this.touchTimeout = setTimeout(() => {
                this.handleTextSelection(e);
                this.isSelecting = false;
            }, 150); // Increased delay for better mobile support
        });

        // Enhanced selection change handling for mobile devices
        document.addEventListener('selectionchange', () => {
            // Handle selection changes with throttling to improve performance
            if (this.selectionTimeout) {
                clearTimeout(this.selectionTimeout);
            }
            
            this.selectionTimeout = setTimeout(() => {
                // This could be used for live preview functionality in the future
            }, 100);
        });

        // Prevent context menu on long press for better UX on mobile
        textContent.addEventListener('contextmenu', (e) => {
            // Allow context menu but prevent it from interfering with highlighting
            setTimeout(() => {
                const selection = window.getSelection();
                if (selection && !selection.isCollapsed) {
                    // There's an active selection, let user choose highlight action
                }
            }, 100);
        });

        // Handle keyboard shortcuts for accessibility
        document.addEventListener('keydown', (e) => {
            if (e.ctrlKey || e.metaKey) {
                switch (e.key) {
                    case 'a':
                        // Allow Ctrl+A but handle it gracefully
                        break;
                    case 'c':
                        // Allow copy functionality
                        break;
                    case 'z':
                        // Could implement undo functionality in the future
                        break;
                }
            }
            
            // ESC key to clear selection
            if (e.key === 'Escape') {
                const selection = window.getSelection();
                if (selection) {
                    selection.removeAllRanges();
                }
            }
        });
    }

    setActiveColor(color) {
        this.selectedColor = color;
        
        // Update button states
        document.querySelectorAll('[data-color]').forEach(button => {
            button.classList.remove('ring-4', 'ring-blue-400');
            if (button.dataset.color === color) {
                button.classList.add('ring-4', 'ring-blue-400');
            }
        });
    }

    async handleTextSelection(event) {
        const selection = window.getSelection();
        
        if (!selection || selection.isCollapsed || selection.rangeCount === 0) {
            return;
        }

        const range = selection.getRangeAt(0);
        const selectedText = selection.toString().trim();
        
        if (selectedText.length === 0) {
            return;
        }

        // Check if the selection is within our text content area
        const textContent = document.getElementById('text-content');
        if (!textContent.contains(range.commonAncestorContainer)) {
            return;
        }

        // Calculate text offsets for API
        const contentElement = textContent.querySelector('.content-text') || textContent;
        const textContent_string = contentElement.textContent;
        const startOffset = this.getTextOffset(contentElement, range.startContainer, range.startOffset);
        const endOffset = startOffset + selectedText.length;

        await this.highlightSelection(range, selectedText, startOffset, endOffset);
        
        // Clear the selection
        selection.removeAllRanges();
    }

    getTextOffset(container, node, offset) {
        let textOffset = 0;
        const walker = document.createTreeWalker(
            container,
            NodeFilter.SHOW_TEXT,
            null,
            false
        );

        let currentNode;
        while (currentNode = walker.nextNode()) {
            if (currentNode === node) {
                return textOffset + offset;
            }
            textOffset += currentNode.textContent.length;
        }
        
        return textOffset;
    }

    async highlightSelection(range, text, startOffset, endOffset) {
        try {
            // If we have a content ID, save to API
            if (this.contentId) {
                await this.saveHighlightToApi(startOffset, endOffset, this.selectedColor);
                // Reload the page to show updated highlights from server
                window.location.reload();
                return;
            }

            // Fallback to local highlighting for demo content
            this.highlightSelectionLocally(range, text);
            
        } catch (error) {
            console.warn('Could not save highlight to API, using local highlighting:', error);
            this.highlightSelectionLocally(range, text);
        }
    }

    async saveHighlightToApi(startOffset, endOffset, color) {
        if (!this.contentId) {
            throw new Error('No content ID available');
        }

        const data = {
            start: startOffset,
            end: endOffset,
            color: color
        };

        return await this.makeApiRequest(`${this.apiBaseUrl}/${this.contentId}/highlights`, 'POST', data);
    }

    highlightSelectionLocally(range, text) {
        try {
            // Create highlight element
            const highlightElement = document.createElement('mark');
            highlightElement.className = `highlight-${this.selectedColor} ${this.getHighlightClasses(this.selectedColor)}`;
            highlightElement.dataset.highlightId = this.highlightId;
            highlightElement.dataset.originalText = text;
            
            // Wrap the selected content
            range.surroundContents(highlightElement);
            
            // Store the highlight
            this.highlights.set(this.highlightId, {
                id: this.highlightId,
                text: text,
                color: this.selectedColor,
                element: highlightElement
            });
            
            // Add click event to remove highlight
            highlightElement.addEventListener('click', (e) => {
                e.stopPropagation();
                this.removeHighlight(this.highlightId);
            });
            
            this.highlightId++;
            
        } catch (error) {
            console.warn('Could not highlight selection:', error);
            // Fallback for complex selections
            this.highlightComplexSelection(range, text);
        }
    }

    highlightComplexSelection(range, text) {
        // For complex selections that can't be wrapped easily
        const highlightElement = document.createElement('mark');
        highlightElement.className = `highlight-${this.selectedColor} ${this.getHighlightClasses(this.selectedColor)}`;
        highlightElement.dataset.highlightId = this.highlightId;
        highlightElement.dataset.originalText = text;
        highlightElement.textContent = text;
        
        try {
            range.deleteContents();
            range.insertNode(highlightElement);
            
            this.highlights.set(this.highlightId, {
                id: this.highlightId,
                text: text,
                color: this.selectedColor,
                element: highlightElement
            });
            
            highlightElement.addEventListener('click', (e) => {
                e.stopPropagation();
                this.removeHighlight(this.highlightId);
            });
            
            this.highlightId++;
        } catch (error) {
            console.error('Failed to highlight complex selection:', error);
        }
    }

    getHighlightClasses(color) {
        const colorClasses = {
            yellow: 'bg-yellow-300 text-black',
            green: 'bg-green-300 text-black',
            blue: 'bg-blue-300 text-black',
            pink: 'bg-pink-300 text-black',
            purple: 'bg-purple-300 text-black'
        };
        
        return `${colorClasses[color]} cursor-pointer hover:opacity-80 transition-opacity rounded px-1`;
    }

    removeHighlight(highlightId) {
        const highlight = this.highlights.get(highlightId);
        if (!highlight) return;
        
        const element = highlight.element;
        const parent = element.parentNode;
        
        if (parent) {
            // Replace the highlight element with its text content
            const textNode = document.createTextNode(highlight.text);
            parent.replaceChild(textNode, element);
            
            // Normalize the parent to merge adjacent text nodes
            parent.normalize();
        }
        
        this.highlights.delete(highlightId);
    }

    async clearAllHighlights() {
        try {
            if (this.contentId) {
                await this.makeApiRequest(`${this.apiBaseUrl}/${this.contentId}/highlights/clear`, 'DELETE');
                // Reload to show updated content
                window.location.reload();
                return;
            }

            // Fallback to local clearing for demo content
            const highlightIds = Array.from(this.highlights.keys());
            highlightIds.forEach(id => this.removeHighlight(id));
        } catch (error) {
            console.error('Failed to clear highlights:', error);
            alert('Failed to clear highlights. Please try again.');
        }
    }

    async exportAsText() {
        try {
            if (this.contentId) {
                const response = await this.makeApiRequest(`${this.apiBaseUrl}/${this.contentId}/export`);
                this.downloadFile(response.data.content, response.data.filename || 'highlighted-text.txt', 'text/plain');
                return;
            }

            // Fallback to local export for demo content
            const textContent = document.getElementById('text-content');
            let content = textContent.innerText;
            
            // Add information about highlights
            if (this.highlights.size > 0) {
                content += '\n\n--- HIGHLIGHTED SECTIONS ---\n';
                this.highlights.forEach((highlight, id) => {
                    content += `\n[${highlight.color.toUpperCase()}] ${highlight.text}`;
                });
            }
            
            this.downloadFile(content, 'highlighted-text.txt', 'text/plain');
        } catch (error) {
            console.error('Failed to export text:', error);
            alert('Failed to export text. Please try again.');
        }
    }

    async exportAsImage() {
        const textContent = document.getElementById('text-content');
        const canvas = document.getElementById('export-canvas');
        const ctx = canvas.getContext('2d');
        
        try {
            // Use html2canvas library if available, otherwise use a simple fallback
            if (typeof html2canvas !== 'undefined') {
                const canvasData = await html2canvas(textContent, {
                    backgroundColor: '#ffffff',
                    scale: 2,
                    useCORS: true
                });
                
                this.downloadCanvas(canvasData, 'highlighted-text.png');
            } else {
                // Fallback: create a simple canvas representation
                this.createSimpleImageExport(textContent, canvas, ctx);
            }
        } catch (error) {
            console.error('Image export failed:', error);
            alert('Image export is not available. Please try exporting as text instead.');
        }
    }

    createSimpleImageExport(element, canvas, ctx) {
        // Simple fallback for image export
        const rect = element.getBoundingClientRect();
        canvas.width = rect.width * 2;
        canvas.height = rect.height * 2;
        
        ctx.scale(2, 2);
        ctx.fillStyle = '#ffffff';
        ctx.fillRect(0, 0, rect.width, rect.height);
        
        ctx.fillStyle = '#000000';
        ctx.font = '16px Arial';
        ctx.textWrap = 'word';
        
        const text = element.innerText;
        const lines = this.wrapText(ctx, text, rect.width - 40);
        
        lines.forEach((line, index) => {
            ctx.fillText(line, 20, 30 + (index * 24));
        });
        
        this.downloadCanvas(canvas, 'highlighted-text.png');
    }

    wrapText(ctx, text, maxWidth) {
        const words = text.split(' ');
        const lines = [];
        let currentLine = words[0];

        for (let i = 1; i < words.length; i++) {
            const word = words[i];
            const width = ctx.measureText(currentLine + ' ' + word).width;
            if (width < maxWidth) {
                currentLine += ' ' + word;
            } else {
                lines.push(currentLine);
                currentLine = word;
            }
        }
        lines.push(currentLine);
        return lines;
    }

    downloadCanvas(canvas, filename) {
        const link = document.createElement('a');
        link.download = filename;
        link.href = canvas.toDataURL();
        link.click();
    }

    downloadFile(content, filename, mimeType) {
        const blob = new Blob([content], { type: mimeType });
        const url = window.URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.download = filename;
        link.click();
        window.URL.revokeObjectURL(url);
    }
}

// Initialize the text highlighter when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('text-content')) {
        window.textHighlighter = new TextHighlighter();
    }
});

// Make it available globally for debugging
window.TextHighlighter = TextHighlighter;