<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class SystemMonitorService
{
    public function getSystemMetrics(): array
    {
        return [
            'cpu' => $this->getCpuUsage(),
            'memory' => $this->getMemoryUsage(),
            'disk' => $this->getDiskUsage(),
            'database' => $this->getDatabaseInfo(),
            'app_status' => $this->getAppStatus(),
            'cache_session' => $this->getCacheSessionInfo(),
        ];
    }

    protected function getDatabaseInfo(): array
    {
        $status = 'success';
        $size = 0;
        $connectionName = config('database.default');
        
        try {
            \Illuminate\Support\Facades\DB::connection()->getPdo();
            
            if ($connectionName === 'mysql') {
                $dbName = config("database.connections.{$connectionName}.database");
                $result = \Illuminate\Support\Facades\DB::select("SELECT SUM(data_length + index_length) AS size FROM information_schema.TABLES WHERE table_schema = ?", [$dbName]);
                if (!empty($result)) {
                    $size = $result[0]->size;
                }
            }
        } catch (\Exception $e) {
            $status = 'danger';
        }
        
        return [
            'status' => $status,
            'driver' => $connectionName,
            'database' => config("database.connections.{$connectionName}.database"),
            'size' => $this->formatBytes($size),
        ];
    }

    protected function getAppStatus(): array
    {
        return [
            'php_version' => phpversion(),
            'laravel_version' => app()->version(),
            'environment' => app()->environment(),
            'debug_mode' => config('app.debug'),
            'uptime' => $this->getServerUptime(),
        ];
    }
    
    protected function getServerUptime(): string
    {
        try {
            if (PHP_OS_FAMILY === 'Windows') {
                $output = shell_exec('powershell -Command "(get-date) - (gcim Win32_OperatingSystem).LastBootUpTime | Select-Object Days, Hours, Minutes | ConvertTo-Json"');
                if ($output) {
                    $data = json_decode($output, true);
                    if ($data) {
                        return "{$data['Days']}h {$data['Hours']}m {$data['Minutes']}s";
                    }
                }
            } else {
                $uptime = shell_exec('cat /proc/uptime');
                if ($uptime) {
                    $uptime = explode(' ', $uptime);
                    $seconds = $uptime[0];
                    $days = floor($seconds / 86400);
                    $hours = floor(($seconds % 86400) / 3600);
                    $minutes = floor(($seconds % 3600) / 60);
                    return "{$days}h {$hours}m {$minutes}s";
                }
            }
        } catch (\Exception $e) {
            // Ignore
        }
        return 'N/A';
    }

    protected function getCacheSessionInfo(): array
    {
        $activeSessions = 0;
        try {
            if (config('session.driver') === 'database') {
                $activeSessions = \Illuminate\Support\Facades\DB::table(config('session.table', 'sessions'))
                    ->where('last_activity', '>=', now()->subMinutes(config('session.lifetime'))->getTimestamp())
                    ->count();
            }
        } catch (\Exception $e) {
            // Ignore
        }

        return [
            'cache_driver' => config('cache.default'),
            'session_driver' => config('session.driver'),
            'active_sessions' => $activeSessions,
        ];
    }

    protected function getCpuUsage(): array
    {
        $percentage = Cache::remember('system_monitor_cpu_pct', 2, function () {
            // ... (keep logic but we will split the cache to separate name and pct)
            $val = 0;
            try {
                if (PHP_OS_FAMILY === 'Windows') {
                    $output = shell_exec('powershell -Command "(Get-CimInstance Win32_Processor | Measure-Object -Property LoadPercentage -Average).Average"');
                    if ($output) {
                        $val = (float) trim($output);
                    }
                } else {
                    if (is_readable('/proc/stat')) {
                        $stat1 = file('/proc/stat');
                        usleep(100000); // 100ms delay to calculate difference
                        $stat2 = file('/proc/stat');
                        
                        $info1 = explode(" ", preg_replace("!cpu +!", "", $stat1[0]));
                        $info2 = explode(" ", preg_replace("!cpu +!", "", $stat2[0]));
                        
                        $dif = [];
                        $dif['user'] = $info2[0] - $info1[0];
                        $dif['nice'] = $info2[1] - $info1[1];
                        $dif['sys'] = $info2[2] - $info1[2];
                        $dif['idle'] = $info2[3] - $info1[3];
                        
                        $total = array_sum($dif);
                        $val = $total > 0 ? 100 * ($total - $dif['idle']) / $total : 0;
                    } else {
                        $load = sys_getloadavg();
                        $val = isset($load[0]) ? (float) $load[0] * 10 : 0; // rough estimation
                    }
                }
            } catch (\Exception $e) {
                // Ignore
            }
            return $val;
        });

        $model = Cache::remember('system_monitor_cpu_model', 3600, function () {
            try {
                if (PHP_OS_FAMILY === 'Windows') {
                    $output = shell_exec('powershell -Command "(Get-CimInstance Win32_Processor | Select-Object -First 1).Name"');
                    // Clean up oh-my-posh errors if any
                    if ($output) {
                        $lines = explode("\n", trim($output));
                        return trim(end($lines));
                    }
                } else {
                    if (is_readable('/proc/cpuinfo')) {
                        $cpuinfo = file_get_contents('/proc/cpuinfo');
                        if (preg_match('/model name\s+:\s+(.*)/i', $cpuinfo, $matches)) {
                            return trim($matches[1]);
                        }
                    }
                }
            } catch (\Exception $e) {}
            return 'Unknown CPU';
        });

        return [
            'percentage' => min((int) $percentage, 100),
            'status' => $percentage > 85 ? 'danger' : ($percentage > 70 ? 'warning' : 'success'),
            'model' => $model,
        ];
    }

    protected function getMemoryUsage(): array
    {
        $data = Cache::remember('system_monitor_memory_usage', 3, function () {
            $total = 0;
            $free = 0;

            try {
                if (PHP_OS_FAMILY === 'Windows') {
                    $output = shell_exec('powershell -Command "Get-CimInstance Win32_OperatingSystem | Select-Object FreePhysicalMemory, TotalVisibleMemorySize | ConvertTo-Json"');
                    if ($output) {
                        // Extract JSON part in case of powershell errors
                        preg_match('/\{[\s\S]*\}/', $output, $matches);
                        if (!empty($matches)) {
                            $json = json_decode($matches[0], true);
                            if (isset($json['TotalVisibleMemorySize']) && isset($json['FreePhysicalMemory'])) {
                                $total = $json['TotalVisibleMemorySize'] * 1024;
                                $free = $json['FreePhysicalMemory'] * 1024;
                            }
                        }
                    }
                } else {
                    if (is_readable('/proc/meminfo')) {
                        $meminfo = file_get_contents('/proc/meminfo');
                        preg_match('/MemTotal:\s+(\d+)\s+kB/', $meminfo, $totalMatches);
                        preg_match('/MemAvailable:\s+(\d+)\s+kB/', $meminfo, $availableMatches);
                        if (empty($availableMatches)) {
                            preg_match('/MemFree:\s+(\d+)\s+kB/', $meminfo, $availableMatches);
                        }

                        if (isset($totalMatches[1]) && isset($availableMatches[1])) {
                            $total = $totalMatches[1] * 1024;
                            $free = $availableMatches[1] * 1024;
                        }
                    }
                }
            } catch (\Exception $e) {}

            return ['total' => $total, 'free' => $free];
        });

        $model = Cache::remember('system_monitor_memory_model', 3600, function () {
            try {
                if (PHP_OS_FAMILY === 'Windows') {
                    $output = shell_exec('powershell -Command "(Get-CimInstance Win32_PhysicalMemory | Select-Object -First 1).Manufacturer"');
                    if ($output) {
                        $lines = explode("\n", trim($output));
                        $brand = trim(end($lines));
                        return $brand ? $brand . ' RAM' : 'Unknown RAM';
                    }
                }
            } catch (\Exception $e) {}
            return 'System Memory';
        });

        $total = $data['total'];
        $free = $data['free'];
        $used = $total - $free;
        $percentage = $total > 0 ? ($used / $total) * 100 : 0;

        return [
            'total' => $total,
            'used' => $used,
            'free' => $free,
            'percentage' => (int) $percentage,
            'status' => $percentage > 85 ? 'danger' : ($percentage > 70 ? 'warning' : 'success'),
            'formatted_total' => $this->formatBytes($total),
            'formatted_used' => $this->formatBytes($used),
            'model' => $model,
        ];
    }

    protected function getDiskUsage(): array
    {
        $data = Cache::remember('system_monitor_disk_usage', 60, function () {
            $path = base_path();
            $total = 0;
            $free = 0;

            try {
                $total = disk_total_space($path);
                $free = disk_free_space($path);
            } catch (\Exception $e) {}

            return ['total' => $total, 'free' => $free];
        });

        $model = Cache::remember('system_monitor_disk_model', 3600, function () {
            try {
                if (PHP_OS_FAMILY === 'Windows') {
                    $output = shell_exec('powershell -Command "(Get-CimInstance Win32_DiskDrive | Select-Object -First 1).Model"');
                    if ($output) {
                        $lines = explode("\n", trim($output));
                        return trim(end($lines));
                    }
                }
            } catch (\Exception $e) {}
            return 'Local Disk';
        });

        $total = $data['total'];
        $free = $data['free'];
        $used = $total - $free;
        $percentage = $total > 0 ? ($used / $total) * 100 : 0;

        return [
            'total' => $total,
            'used' => $used,
            'free' => $free,
            'percentage' => (int) $percentage,
            'status' => $percentage > 85 ? 'danger' : ($percentage > 70 ? 'warning' : 'success'),
            'formatted_total' => $this->formatBytes($total),
            'formatted_used' => $this->formatBytes($used),
            'model' => $model,
        ];
    }

    protected function formatBytes($bytes, $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
