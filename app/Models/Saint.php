<?php

namespace App\Models;

use App\Utils\DateFormatter;
use Illuminate\Database\Eloquent\Model;

class Saint extends Model
{
    protected $primaryKey = 'saint_id';
    public $timestamps = true;

    protected array $dates = ['recurrence_date', 'feast_day'];

    public function toArray(): array
    {
        $array = parent::toArray();
        $array['recurrence_date'] = DateFormatter::formatDate($this->recurrence_date);
        $array['feast_day'] = DateFormatter::formatDate($this->feast_day);
        $array['created_at'] = DateFormatter::formatDateTime($this->created_at);
        $array['updated_at'] = DateFormatter::formatDateTime($this->updated_at);
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
