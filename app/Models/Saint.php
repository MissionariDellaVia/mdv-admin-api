<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;

class Saint extends Model
{
    protected $primaryKey = 'saint_id';
    public $timestamps = true;

    protected array $dates = ['recurrence_date', 'feast_day'];

    public function toArray(): array
    {
        $array = parent::toArray();
        $array['recurrence_date'] = $this->recurrence_date ? $this->recurrence_date->format('Y-m-d') : null;
        $array['feast_day'] = $this->feast_day ? $this->feast_day->format('Y-m-d') : null;
        return $array;
    }

    protected $fillable = [
        'name',
        'biography',
        'recurrence_date',
        'feast_day',
        'is_active'
    ];

    protected $casts = [
        'recurrence_date' => 'date',
        'feast_day' => 'date',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

}
