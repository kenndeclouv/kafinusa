<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MonthlyBackup extends Model
{
    protected $fillable = [
        'month',
        'locked_at',
        'file_path',
    ];

    protected $casts = [
        'locked_at' => 'datetime',
    ];

    public static function isLocked($date): bool
    {
        $month = \Carbon\Carbon::parse($date)->format('Y-m');
        
        return \Illuminate\Support\Facades\Cache::remember("is_locked_{$month}", now()->addHours(24), function () use ($month) {
            return self::where('month', $month)->whereNotNull('locked_at')->exists();
        });
    }
}
