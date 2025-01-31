<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Address extends Model
{
    protected $primaryKey = 'address_id';
    public $timestamps = true;

    protected $fillable = [
        'street',
        'city',
        'state',
        'postal_code',
        'country',
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
        return $this->hasMany(Contact::class, 'address_id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class, 'address_id');
    }
}
