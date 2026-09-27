<?php

$failed = false;
$directories = ['app', 'routes', 'config', 'database'];

foreach ($directories as $dir) {
    if (!is_dir($dir)) continue;

    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    
    foreach ($iterator as $file) {
        if ($file->getExtension() === 'php') {
            exec('php -l ' . escapeshellarg($file->getPathname()) . ' 2>&1', $output, $returnVar);
            if ($returnVar !== 0) {
                echo implode(PHP_EOL, $output) . PHP_EOL;
                $failed = true;
            }
            $output = [];
        }
    }
}

if (!$failed) {
    echo "No syntax errors found in PHP files." . PHP_EOL;
}

exit($failed ? 1 : 0);
