<?php

namespace App\Models;

use App\Utils\DateFormatter;
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

    public function toArray(): array
    {
        $array = parent::toArray();
        $array['created_at'] = DateFormatter::formatDateTime($this->created_at);
        $array['updated_at'] = DateFormatter::formatDateTime($this->updated_at);
        return $array;
    }

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
