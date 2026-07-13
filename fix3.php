<?php
$file = 'ice-ration/resources/views/agent/deliveries.blade.php';
$content = file_get_contents($file);

$content = str_replace(
    "<p class=\"font-bold text-slate-800\">{{ \$delivery->manager->name ?? 'Unknown manager' }}</p>",
    "<p class=\"font-bold text-slate-800\">\n                        {{ \$delivery->manager->name ?? 'Unknown manager' }}\n                        @if(\$delivery->truck_plate)\n                            <span class=\"text-sm font-normal text-slate-500 ml-2\">({{ \$delivery->truck_plate }})</span>\n                        @endif\n                    </p>",
    $content
);

file_put_contents($file, $content);
