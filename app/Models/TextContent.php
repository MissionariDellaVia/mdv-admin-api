<?php

namespace App\Models;

use App\Utils\DateFormatter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class TextContent extends Model
{
    protected $table = 'text_contents';
    protected $primaryKey = 'content_id';
    public $timestamps = true;

    protected $fillable = [
        'title',
        'slug',
        'content',
        'is_published'
    ];

    protected $casts = [
        'is_published' => 'boolean',
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
}
