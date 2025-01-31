<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Comment extends Model
{
    protected $primaryKey = 'comment_id';
    public $timestamps = true;

    protected $fillable = [
        'gospel_id',
        'comment_text',
        'extra_info',
        'youtube_link',
        'comment_order',
        'is_latest'
    ];

    protected $casts = [
        'is_latest' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function gospel(): BelongsTo
    {
        return $this->belongsTo(Gospel::class, 'gospel_id');
    }
}
