<?php

namespace App\Models;

use App\Utils\DateFormatter;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Event extends Model
{
    protected $primaryKey = 'event_id';
    public $timestamps = true;

    protected $fillable = [
        'title',
        'description',
        'start_date',
        'end_date',
        'start_time',
        'end_time',
        'place',
        'is_holy_mass',
        'is_recurring',
        'recurrence_pattern',
        'place_id'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'is_holy_mass' => 'boolean',
        'is_recurring' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function toArray(): array
    {
        $array = parent::toArray();
        $array['start_date'] = DateFormatter::formatDate($this->start_date);
        $array['end_date'] = DateFormatter::formatDate($this->end_date);
        $array['start_time'] = DateFormatter::formatDateTime($this->start_time);
        $array['end_time'] = DateFormatter::formatDateTime($this->end_time);
        $array['created_at'] = DateFormatter::formatDateTime($this->created_at);
        $array['updated_at'] = DateFormatter::formatDateTime($this->updated_at);
        return $array;
    }

    public function place(): BelongsTo
    {
        return $this->belongsTo(Place::class, 'place_id');
    }
}
