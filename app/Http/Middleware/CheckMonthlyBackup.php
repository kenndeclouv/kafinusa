<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Cache;
use App\Models\MonthlyBackup;

class CheckMonthlyBackup
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Auto-lock bulan lalu hanya berjalan mulai tanggal 3 (setelah proses muat & retur tanggal 1 & 2 selesai)
        if (now()->day < 3) {
            return $next($request);
        }

        $lastMonth = now()->subMonth()->format('Y-m');
        $cacheKey = "monthly_backup_processed_{$lastMonth}";

        if (!Cache::has($cacheKey)) {
            $backupExists = MonthlyBackup::where('month', $lastMonth)->exists();
            
            if (!$backupExists) {
                MonthlyBackup::create([
                    'month' => $lastMonth,
                    'locked_at' => now(),
                ]);

                // Bersihkan cache agar status terkunci langsung sinkron seketika
                Cache::forget("is_locked_{$lastMonth}");
                Cache::forget('all_locked_months');
            }
            
            // Mark as processed
            Cache::put($cacheKey, true, now()->addYears(1));
        }

        return $next($request);
    }
}
