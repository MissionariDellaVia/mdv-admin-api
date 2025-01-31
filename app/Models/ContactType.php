<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ContactType extends Model
{
    protected $primaryKey = 'contact_type_id';
    public $timestamps = true;

    protected $fillable = [
        'type_name',
        'description',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function contacts(): HasMany
    {
        return $this->hasMany(Contact::class, 'contact_type_id');
    }
}
