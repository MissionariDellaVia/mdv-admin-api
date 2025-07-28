<?php

namespace App\Models;

use App\Utils\DateFormatter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class TextContent extends Model
{
    use HasFactory;
    protected $table = 'text_contents';
    protected $primaryKey = 'content_id';
    public $timestamps = true;

    protected $fillable = [
        'title',
        'slug',
        'content',
        'highlights',
        'is_published'
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'highlights' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function toArray(): array
    {
        $array = parent::toArray();
        $array['created_at'] = DateFormatter::formatDateTime($this->created_at);
        $array['updated_at'] = DateFormatter::formatDateTime($this->updated_at);
        return $array;
    }

    // Automatically generate slug from title
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($textContent) {
            if (empty($textContent->slug)) {
                $textContent->slug = Str::slug($textContent->title);
            }
        });
    }

    // Get content by slug
    public static function findBySlug($slug)
    {
        return static::where('slug', $slug)->first();
    }

    // Scope for published content
    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    /**
     * Add a highlight to the content
     */
    public function addHighlight(int $startOffset, int $endOffset, string $color): bool
    {
        $highlights = $this->highlights ?? [];
        
        // Validate highlight data
        if ($startOffset >= $endOffset || $startOffset < 0 || $endOffset > strlen($this->content)) {
            return false;
        }
        
        $highlight = [
            'start' => $startOffset,
            'end' => $endOffset,
            'color' => $color,
            'text' => substr($this->content, $startOffset, $endOffset - $startOffset),
            'created_at' => now()->toISOString()
        ];
        
        $highlights[] = $highlight;
        $this->highlights = $highlights;
        
        return $this->save();
    }

    /**
     * Remove a highlight by index
     */
    public function removeHighlight(int $index): bool
    {
        $highlights = $this->highlights ?? [];
        
        if (!isset($highlights[$index])) {
            return false;
        }
        
        unset($highlights[$index]);
        $this->highlights = array_values($highlights); // Re-index array
        
        return $this->save();
    }

    /**
     * Clear all highlights
     */
    public function clearHighlights(): bool
    {
        $this->highlights = [];
        return $this->save();
    }

    /**
     * Get content with highlights applied as HTML
     */
    public function getHighlightedContent(): string
    {
        $content = $this->content;
        $highlights = $this->highlights ?? [];
        
        if (empty($highlights)) {
            return $content;
        }
        
        // Sort highlights by start position (descending) to avoid offset issues
        usort($highlights, fn($a, $b) => $b['start'] <=> $a['start']);
        
        foreach ($highlights as $highlight) {
            $text = $highlight['text'];
            $color = $highlight['color'];
            $start = $highlight['start'];
            $end = $highlight['end'];
            
            $before = substr($content, 0, $start);
            $after = substr($content, $end);
            $highlighted = "<span class=\"highlight highlight-{$color}\">{$text}</span>";
            
            $content = $before . $highlighted . $after;
        }
        
        return $content;
    }

    /**
     * Export content with highlights as plain text
     */
    public function exportHighlightedText(): string
    {
        $content = $this->content;
        $highlights = $this->highlights ?? [];
        
        if (empty($highlights)) {
            return $content;
        }
        
        $export = "Title: {$this->title}\n";
        $export .= "Created: " . $this->created_at->format('Y-m-d H:i:s') . "\n";
        $export .= str_repeat('-', 50) . "\n\n";
        $export .= $content . "\n\n";
        $export .= "HIGHLIGHTS:\n";
        $export .= str_repeat('-', 20) . "\n";
        
        foreach ($highlights as $index => $highlight) {
            $export .= ($index + 1) . ". [{$highlight['color']}] {$highlight['text']}\n";
        }
        
        return $export;
    }
}
