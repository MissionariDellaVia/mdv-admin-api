<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Gospel extends Model
{
    protected $primaryKey = 'gospel_id';
    public $timestamps = true;

    protected $fillable = [
        'gospel_verse',
        'gospel_text',
        'evangelist',
        'sacred_text_reference',
        'liturgical_period',
        'latest_comment_id',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class, 'gospel_id');
    }

    public function gospelWays(): HasMany
    {
        return $this->hasMany(GospelWay::class, 'gospel_id');
    }
}
