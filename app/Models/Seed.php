<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Seed extends Model
{
    protected $primaryKey = 'seed_id';
    public $timestamps = true;

    protected $fillable = [
        'verse_text',
        'color',
        'reference'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // Scope for specific colors
    public function scopeByColor($query, $color)
    {
        return $query->where('color', $color);
    }

    // Get random verse
    public static function getRandomVerse()
    {
        return static::inRandomOrder()->first();
    }

    // Get verses by reference
    public function scopeByReference($query, $reference)
    {
        return $query->where('reference', 'like', "%{$reference}%");
    }
}
