<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WeeklyCheckIn extends Model
{
    protected $fillable = [
        'user_id',
        'check_in_date',
        'weight',
        'energy_level',
        'mood_level',
        'sleep_quality',
        'notes',
        'photo_path',
        'photo_mime',
        'photo_size',
        'photo_data',
    ];

    protected function casts(): array
    {
        return [
            'check_in_date' => 'date',
            'weight' => 'decimal:2',
            'energy_level' => 'integer',
            'mood_level' => 'integer',
            'sleep_quality' => 'integer',
            'photo_size' => 'integer',
        ];
    }

    public function hasPhoto(): bool
    {
        return !empty($this->photo_size) || !empty($this->photo_data) || !empty($this->photo_path);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
