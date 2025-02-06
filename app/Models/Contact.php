<?php

namespace App\Models;

use App\Utils\DateFormatter;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Contact extends Model
{
    protected $primaryKey = 'contact_id';
    public $timestamps = true;

    protected $fillable = [
        'contact_type',
        'contact_group',
        'contact_type_description',
        'contact_value',
        'place_id',
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

    public function place(): BelongsTo
    {
        return $this->belongsTo(Place::class, 'place_id');
    }
}
