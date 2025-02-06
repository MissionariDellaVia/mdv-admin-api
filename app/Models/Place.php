<?php

namespace App\Models;

use App\Utils\DateFormatter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Place extends Model
{
    protected $primaryKey = 'place_id';
    public $timestamps = true;

    protected $fillable = [
        'name',
        'street',
        'city',
        'state',
        'postal_code',
        'latitude',
        'longitude'
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
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

    public function contacts(): HasMany
    {
        return $this->hasMany(Contact::class, 'place_id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class, 'place_id');
    }
}
