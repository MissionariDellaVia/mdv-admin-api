<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Media extends Model
{
    protected $table = 'media';
    protected $primaryKey = 'media_id';
    public $timestamps = true;

    protected $fillable = [
        'title',
        'file_name',
        'file_path',
        'mime_type',
        'file_size',
        'alt_text',
        'description'
    ];

    protected $casts = [
        'file_size' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // Get full URL for the media file
    public function getUrlAttribute(): string
    {
        return Storage::url($this->file_path);
    }

    // Get formatted file size
    public function getFormattedSizeAttribute(): string
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);

        return round($bytes, 2) . ' ' . $units[$pow];
    }

    // Scope for images
    public function scopeImages($query)
    {
        return $query->where('mime_type', 'like', 'image%');
    }

    // Scope for documents
    public function scopeDocuments($query)
    {
        return $query->where('mime_type', 'like', 'application%');
    }
}
