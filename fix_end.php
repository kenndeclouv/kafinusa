<?php
$file = 'resources/views/livewire/reports/partials/print-production-circulation-table.blade.php';
$content = file_get_contents($file);

$old = <<<BLADE
                    @php
                        \$lastDayData = end(\$this->circulationData['daily']);
                    @endphp
BLADE;

$new = <<<BLADE
                    @php
                        \$circ = \$this->circulationData;
                        \$lastDayData = end(\$circ['daily']);
                    @endphp
BLADE;

$content = str_replace($old, $new, $content);
file_put_contents($file, $content);
echo "Fixed end() error";
