<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class WorkoutPlan extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'rotation',
        'rest_days',
        'current_index',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'rotation' => 'array',
            'rest_days' => 'array',
            'current_index' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function nextWorkoutName(): ?string
    {
        $rotation = collect($this->rotation ?? [])
            ->filter()
            ->values();

        if ($rotation->isEmpty()) {
            return null;
        }

        return $rotation[$this->current_index % $rotation->count()];
    }

    public function advance(): void
    {
        $count = count($this->rotation ?? []);

        if ($count < 1) {
            return;
        }

        $this->update([
            'current_index' => ($this->current_index + 1) % $count,
        ]);
    }

    public function advanceAfterWorkoutName(string $name): void
    {
        $rotation = collect($this->rotation ?? [])
            ->filter()
            ->values();

        if ($rotation->isEmpty()) {
            return;
        }

        $normalizedName = Str::lower(trim($name));
        $savedIndex = $rotation->search(function ($item) use ($normalizedName) {
            return Str::lower(trim((string) $item)) === $normalizedName;
        });

        if ($savedIndex === false) {
            return;
        }

        $this->update([
            'current_index' => ($savedIndex + 1) % $rotation->count(),
        ]);
    }
}
