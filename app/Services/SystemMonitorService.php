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
        ];
    }

    protected function getCpuUsage(): array
    {
        $percentage = Cache::remember('system_monitor_cpu', 2, function () {
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

        return [
            'percentage' => min((int) $percentage, 100),
            'status' => $percentage > 85 ? 'danger' : ($percentage > 70 ? 'warning' : 'success'),
        ];
    }

    protected function getMemoryUsage(): array
    {
        $data = Cache::remember('system_monitor_memory', 3, function () {
            $total = 0;
            $free = 0;

            try {
                if (PHP_OS_FAMILY === 'Windows') {
                    $output = shell_exec('powershell -Command "Get-CimInstance Win32_OperatingSystem | Select-Object FreePhysicalMemory, TotalVisibleMemorySize | ConvertTo-Json"');
                    if ($output) {
                        $data = json_decode($output, true);
                        if (isset($data['TotalVisibleMemorySize']) && isset($data['FreePhysicalMemory'])) {
                            // Values are in KB
                            $total = $data['TotalVisibleMemorySize'] * 1024;
                            $free = $data['FreePhysicalMemory'] * 1024;
                        }
                    }
                } else {
                    // Linux Support
                    if (is_readable('/proc/meminfo')) {
                        $meminfo = file_get_contents('/proc/meminfo');
                        preg_match('/MemTotal:\s+(\d+)\s+kB/', $meminfo, $totalMatches);
                        preg_match('/MemAvailable:\s+(\d+)\s+kB/', $meminfo, $availableMatches);
                        
                        // Fallback to MemFree if MemAvailable is not present (older kernels)
                        if (empty($availableMatches)) {
                            preg_match('/MemFree:\s+(\d+)\s+kB/', $meminfo, $availableMatches);
                        }

                        if (isset($totalMatches[1]) && isset($availableMatches[1])) {
                            $total = $totalMatches[1] * 1024;
                            $free = $availableMatches[1] * 1024;
                        }
                    }
                }
            } catch (\Exception $e) {
                // Ignore
            }

            return ['total' => $total, 'free' => $free];
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
        ];
    }

    protected function getDiskUsage(): array
    {
        $data = Cache::remember('system_monitor_disk', 60, function () {
            $path = base_path();
            $total = 0;
            $free = 0;

            try {
                $total = disk_total_space($path);
                $free = disk_free_space($path);
            } catch (\Exception $e) {
                // Ignore
            }

            return ['total' => $total, 'free' => $free];
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
