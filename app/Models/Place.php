<?php

namespace App\Models;

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

    public function contacts(): HasMany
    {
        return $this->hasMany(Contact::class, 'place_id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class, 'place_id');
    }
}
