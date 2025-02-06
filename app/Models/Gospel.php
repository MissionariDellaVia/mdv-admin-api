<?php

namespace App\Models;

use App\Utils\DateFormatter;
use Carbon\Carbon;
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

    public function toArray(): array
    {
        $array = parent::toArray();
        $array['created_at'] = DateFormatter::formatDateTime($this->created_at);
        $array['updated_at'] = DateFormatter::formatDateTime($this->updated_at);
        return $array;
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class, 'gospel_id');
    }

    public function gospelWays(): HasMany
    {
        return $this->hasMany(GospelWay::class, 'gospel_id');
    }
}
