<?php

namespace App\Models;

use App\Utils\DateFormatter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GospelCommentary extends Model
{
    use HasFactory;

    protected $table = 'gospel_commentary';
    protected $primaryKey = 'commentary_id';
    public $timestamps = true;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int,string>
     */
    protected $fillable = [
        'calendar_date',
        'gospel_id',
        'saint_id',
        'comment_text',
        'extra_info',
        'youtube_link',
        'liturgical_season'
    ];

    public function toArray(): array
    {
        $array = parent::toArray();
        $array['calendar_date'] = DateFormatter::formatDate($this->calendar_date);
        $array['created_at'] = DateFormatter::formatDateTime($this->created_at);
        $array['updated_at'] = DateFormatter::formatDateTime($this->updated_at);
        return $array;
    }

    /**
     * Get the gospel that owns the commentary.
     */
    public function gospel(): BelongsTo
    {
        return $this->belongsTo(Gospel::class, 'gospel_id');
    }

    /**
     * Get the saint associated with the commentary.
     */
    public function saint(): BelongsTo
    {
        return $this->belongsTo(Saint::class, 'saint_id');
    }
}
