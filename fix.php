<?php
$files = [
    'ice-ration/database/migrations/0001_01_01_000000_create_users_table.php' => [
        'truck_driver' => 'truck_manager'
    ],
    'ice-ration/database/migrations/2024_01_01_000030_create_deliveries_table.php' => [
        'driver_id' => 'manager_id',
        "\$table->integer('blocks_delivered');" => "\$table->string('truck_plate', 50)->nullable();\n            \$table->integer('blocks_delivered');"
    ],
    'ice-ration/app/Models/User.php' => [
        'ROLE_TRUCK_DRIVER' => 'ROLE_TRUCK_MANAGER',
        'driver_id' => 'manager_id',
        'isTruckDriver' => 'isTruckManager',
        'truck_driver' => 'truck_manager'
    ],
    'ice-ration/app/Models/Delivery.php' => [
        'driver_id' => 'manager_id',
        'driver()' => 'manager()',
        "'manager_id'," => "'manager_id',\n        'truck_plate',"
    ],
    'ice-ration/database/factories/UserFactory.php' => [
        'ROLE_TRUCK_DRIVER' => 'ROLE_TRUCK_MANAGER',
        'truckDriver' => 'truckManager'
    ],
    'ice-ration/database/factories/DeliveryFactory.php' => [
        'driver_id' => 'manager_id',
        'truckDriver()' => 'truckManager()'
    ],
    'ice-ration/database/seeders/DemoStaffSeeder.php' => [
        'Driver 1' => 'Manager 1',
        'Driver 2' => 'Manager 2',
        'ROLE_TRUCK_DRIVER' => 'ROLE_TRUCK_MANAGER'
    ],
    'ice-ration/app/Http/Controllers/Auth/LoginController.php' => [
        '/driver/dashboard' => '/manager/dashboard',
        'ROLE_TRUCK_DRIVER' => 'ROLE_TRUCK_MANAGER'
    ],
    'ice-ration/app/Http/Controllers/Admin/UserController.php' => [
        'ROLE_TRUCK_DRIVER' => 'ROLE_TRUCK_MANAGER',
        'truck_driver' => 'truck_manager'
    ],
    'ice-ration/resources/views/admin/users/index.blade.php' => [
        'truck_driver' => 'truck_manager',
        'Truck Driver' => 'Truck Manager'
    ],
    'ice-ration/resources/views/admin/users/form.blade.php' => [
        'truck_driver' => 'truck_manager',
        'Truck Driver' => 'Truck Manager'
    ],
    'ice-ration/app/Http/Controllers/Manager/DeliveryController.php' => [
        'namespace App\Http\Controllers\Driver;' => 'namespace App\Http\Controllers\Manager;',
        'driver_id' => 'manager_id',
        'driver.dashboard' => 'manager.dashboard',
        'driver.history' => 'manager.history'
    ],
    'ice-ration/resources/views/manager/dashboard.blade.php' => [
        'driver.deliveries' => 'manager.deliveries',
        'driver.dashboard' => 'manager.dashboard',
        'Notes (optional)' => 'Truck Plate / Identifier',
        'name="notes"' => 'name="truck_plate"',
        'old(\'notes\')' => 'old(\'truck_plate\')'
    ],
    'ice-ration/resources/views/manager/history.blade.php' => [
        'driver.dashboard' => 'manager.dashboard'
    ],
    'ice-ration/routes/web.php' => [
        'Driver\DeliveryController' => 'Manager\DeliveryController',
        'ROLE_TRUCK_DRIVER' => 'ROLE_TRUCK_MANAGER',
        "prefix('driver')" => "prefix('manager')",
        "name('driver.')" => "name('manager.')"
    ],
    'ice-ration/resources/views/agent/deliveries.blade.php' => [
        'delivery->driver->name' => 'delivery->manager->name',
        'Unknown driver' => 'Unknown manager'
    ],
    'ice-ration/tests/Feature/PanelSmokeTest.php' => [
        '/driver/' => '/manager/',
        'truckDriver' => 'truckManager',
        'truck_driver' => 'truck_manager',
        'test_truck_driver_panel_pages_render' => 'test_truck_manager_panel_pages_render'
    ]
];

foreach ($files as $file => $replacements) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        foreach ($replacements as $search => $replace) {
            $content = str_replace($search, $replace, $content);
        }
        file_put_contents($file, $content);
    } else {
        echo "Missing: $file\n";
    }
}
