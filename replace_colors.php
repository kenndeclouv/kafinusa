<?php
$file = 'resources/views/livewire/reports/partials/print-production-circulation-table.blade.php';
$content = file_get_contents($file);

$replacements = [
    ' color: #047857; background: #ecfdf5;' => '',
    ' color: #047857;' => '',
    ' background: #ecfdf5;' => '',
    ' background-color: #f3f4f6;' => '',
    ' background: #e5e7eb;' => '',
    ' color: #b91c1c;' => '',
    ' color: #1d4ed8;' => '',
    ' color: #7e22ce;' => '',
    ' color: #0f766e; background: #f0fdfa;' => '',
    ' color: #0f766e;' => '',
    ' background: #f0fdfa;' => '',
    ' background: #faf5ff;' => '',
    ' color: #059669; background: #ecfdf5;' => '',
    ' color: #b91c1c; background: #fef2f2;' => '',
    ' color: #1d4ed8; background: #eff6ff;' => '',
    ' color: #7e22ce; background: #faf5ff;' => '',
];

$content = str_replace(array_keys($replacements), array_values($replacements), $content);

file_put_contents($file, $content);
echo "Colors removed";
