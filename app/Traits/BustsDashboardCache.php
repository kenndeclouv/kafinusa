<?php

namespace App\Traits;

use Illuminate\Support\Facades\Cache;

trait BustsDashboardCache
{
    protected static function bootBustsDashboardCache()
    {
        static::saved(function ($model) {
            Cache::put('dashboard_cache_version', time());
        });

        static::deleted(function ($model) {
            Cache::put('dashboard_cache_version', time());
        });
    }
}
