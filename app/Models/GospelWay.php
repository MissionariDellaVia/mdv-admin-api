<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GospelWay extends Model
{
    protected $table = 'gospel_way';
    protected $primaryKey = 'gospel_way_id';
    public $timestamps = true;

    protected $fillable = [
        'calendar_date',
        'gospel_id',
        'saint_id',
        'liturgical_season',
        'is_solemnity',
        'is_feast',
        'is_memorial'
    ];

    protected $casts = [
        'calendar_date' => 'date',
        'is_solemnity' => 'boolean',
        'is_feast' => 'boolean',
        'is_memorial' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function gospel(): BelongsTo
    {
        return $this->belongsTo(Gospel::class, 'gospel_id');
    }

    public function saint(): BelongsTo
    {
        return $this->belongsTo(Saint::class, 'saint_id');
    }
}
