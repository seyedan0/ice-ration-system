<?php
$file = 'ice-ration/app/Http/Controllers/Manager/DeliveryController.php';
$content = file_get_contents($file);

$content = str_replace(
    "'notes' => ['nullable', 'string', 'max:255'],",
    "'truck_plate' => ['nullable', 'string', 'max:50'],\n            'notes' => ['nullable', 'string', 'max:255'],",
    $content
);

$content = str_replace(
    "'notes' => \$data['notes'] ?? null,",
    "'truck_plate' => \$data['truck_plate'] ?? null,\n            'notes' => \$data['notes'] ?? null,",
    $content
);

file_put_contents($file, $content);
