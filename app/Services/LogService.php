<?php

namespace App\Services;

use Illuminate\Support\Facades\File;

class LogService
{
    /**
     * Get list of log files with metadata.
     */
    public function getLogFiles(): array
    {
        $logFiles = File::files(storage_path('logs'));
        $logs = [];

        foreach ($logFiles as $file) {
            if ($file->getExtension() !== 'log') {
                continue;
            }

            $logs[] = [
                'name' => $file->getFilename(),
                'size' => round($file->getSize() / 1024, 2), // KB
                'modified' => $file->getMTime(),
            ];
        }

        // Sort by modified time descending
        usort($logs, function ($a, $b) {
            return $b['modified'] <=> $a['modified'];
        });

        return $logs;
    }

    /**
     * Calculate statistics from all log files.
     */
    public function getStats(): array
    {
        $files = File::files(storage_path('logs'));
        $stats = [
            'local' => 0,
            'production' => 0,
            'error' => 0,
            'warning' => 0,
            'info' => 0,
        ];

        foreach ($files as $file) {
            if ($file->getExtension() !== 'log') {
                continue;
            }

            // Read the last 1000 lines or full content if small?
            // For performance, maybe limit? The user's code reads full content.
            // Let's stick to user's logic for now but be mindful of large files.
            $content = File::get($file);
            $lines = explode("\n", $content);

            foreach ($lines as $line) {
                if (preg_match('/^\[(.*?)\] (\w+)\.(\w+): (.+)$/', $line, $matches)) {
                    $env = strtolower($matches[2] ?? 'unknown');
                    $type = strtolower($matches[3] ?? 'unknown');

                    if (isset($stats[$env])) {
                        $stats[$env]++;
                    }
                    if (isset($stats[$type])) {
                        $stats[$type]++;
                    }
                }
            }
        }

        return $stats;
    }

    /**
     * Get parsed content of a specific log file.
     */
    public function getLogContent(string $filename): array
    {
        $filePath = storage_path("logs/{$filename}");

        if (! File::exists($filePath)) {
            return [];
        }

        $content = File::get($filePath);

        return $this->parseLog($content);
    }

    /**
     * Delete a log file.
     */
    public function deleteLogFile(string $filename): bool
    {
        $filePath = storage_path("logs/{$filename}");

        if (File::exists($filePath)) {
            return File::delete($filePath);
        }

        return false;
    }

    /**
     * Download a log file.
     */
    public function download(string $filename)
    {
        $filePath = storage_path("logs/{$filename}");

        if (File::exists($filePath)) {
            return response()->download($filePath);
        }

        return null;
    }

    /**
     * Parse log content into structured entries.
     */
    private function parseLog(string $content): array
    {
        $logEntries = [];
        $lines = explode("\n", $content);
        $currentEntry = '';

        foreach ($lines as $line) {
            if (preg_match('/^\[\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\]/', $line)) {
                if (! empty($currentEntry)) {
                    $logEntries[] = $this->formatLogEntry($currentEntry);
                }
                $currentEntry = $line;
            } else {
                $currentEntry .= "\n".$line;
            }
        }

        if (! empty($currentEntry)) {
            $logEntries[] = $this->formatLogEntry($currentEntry);
        }

        // Sort by timestamp descending
        usort($logEntries, function ($a, $b) {
            return strcmp($b['timestamp'], $a['timestamp']);
        });

        return $logEntries;
    }

    private function formatLogEntry(string $entry): array
    {
        preg_match('/^\[(.*?)\] (\w+)\.(\w+): (.+)$/s', $entry, $matches);

        return [
            'timestamp' => $matches[1] ?? 'Unknown',
            'env' => $matches[2] ?? 'Unknown',
            'type' => $matches[3] ?? 'Unknown',
            'message' => $matches[4] ?? $entry,
            'full_entry' => $entry,
        ];
    }
}
