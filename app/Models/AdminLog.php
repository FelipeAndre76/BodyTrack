<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminLog extends Model
{
    protected $fillable = [
        'admin_id',
        'action',
        'description'
    ];

    public function admin()
    {
        return $this->belongsTo(
            User::class,
            'admin_id'
        );
    }

    private function logAction(string $action, string $description): void
{
    AdminLog::create([
        'admin_id' => auth()->id(),
        'action' => $action,
        'description' => $description,
    ]);
}
}
