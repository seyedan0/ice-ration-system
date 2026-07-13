#!/bin/bash
sed -i 's/truck_driver/truck_manager/g' ice-ration/database/migrations/0001_01_01_000000_create_users_table.php

sed -i 's/driver_id/manager_id/g' ice-ration/database/migrations/2024_01_01_000030_create_deliveries_table.php
sed -i '/$table->integer('\''blocks_delivered\'');/i \            $table->string('\''truck_plate\'', 50)->nullable();' ice-ration/database/migrations/2024_01_01_000030_create_deliveries_table.php

sed -i 's/ROLE_TRUCK_DRIVER/ROLE_TRUCK_MANAGER/g' ice-ration/app/Models/User.php
sed -i 's/driver_id/manager_id/g' ice-ration/app/Models/User.php
sed -i 's/isTruckDriver/isTruckManager/g' ice-ration/app/Models/User.php
sed -i 's/truck_driver/truck_manager/g' ice-ration/app/Models/User.php

sed -i 's/driver_id/manager_id/g' ice-ration/app/Models/Delivery.php
sed -i 's/driver()/manager()/g' ice-ration/app/Models/Delivery.php
sed -i '/'\''manager_id'\'',/a \        '\''truck_plate'\'',' ice-ration/app/Models/Delivery.php

sed -i 's/ROLE_TRUCK_DRIVER/ROLE_TRUCK_MANAGER/g' ice-ration/database/factories/UserFactory.php
sed -i 's/truckDriver/truckManager/g' ice-ration/database/factories/UserFactory.php

sed -i 's/driver_id/manager_id/g' ice-ration/database/factories/DeliveryFactory.php
sed -i 's/truckDriver()/truckManager()/g' ice-ration/database/factories/DeliveryFactory.php

sed -i 's/Driver 1/Manager 1/g' ice-ration/database/seeders/DemoStaffSeeder.php
sed -i 's/Driver 2/Manager 2/g' ice-ration/database/seeders/DemoStaffSeeder.php
sed -i 's/ROLE_TRUCK_DRIVER/ROLE_TRUCK_MANAGER/g' ice-ration/database/seeders/DemoStaffSeeder.php

sed -i 's/\/driver\//\/manager\//g' ice-ration/app/Http/Controllers/Auth/LoginController.php
sed -i 's/ROLE_TRUCK_DRIVER/ROLE_TRUCK_MANAGER/g' ice-ration/app/Http/Controllers/Auth/LoginController.php

sed -i 's/ROLE_TRUCK_DRIVER/ROLE_TRUCK_MANAGER/g' ice-ration/app/Http/Controllers/Admin/UserController.php
sed -i 's/truck_driver/truck_manager/g' ice-ration/app/Http/Controllers/Admin/UserController.php

sed -i 's/truck_driver/truck_manager/g' ice-ration/resources/views/admin/users/index.blade.php
sed -i 's/Truck Driver/Truck Manager/g' ice-ration/resources/views/admin/users/index.blade.php

sed -i 's/truck_driver/truck_manager/g' ice-ration/resources/views/admin/users/form.blade.php
sed -i 's/Truck Driver/Truck Manager/g' ice-ration/resources/views/admin/users/form.blade.php

sed -i 's/namespace App\Http\Controllers\Driver;/namespace App\Http\Controllers\Manager;/g' ice-ration/app/Http/Controllers/Manager/DeliveryController.php
sed -i 's/driver_id/manager_id/g' ice-ration/app/Http/Controllers/Manager/DeliveryController.php
sed -i 's/driver\.dashboard/manager.dashboard/g' ice-ration/app/Http/Controllers/Manager/DeliveryController.php
sed -i 's/driver\.history/manager.history/g' ice-ration/app/Http/Controllers/Manager/DeliveryController.php

sed -i 's/driver\.deliveries/manager.deliveries/g' ice-ration/resources/views/manager/dashboard.blade.php
sed -i 's/driver\.dashboard/manager.dashboard/g' ice-ration/resources/views/manager/history.blade.php

sed -i 's/Driver\DeliveryController/Manager\DeliveryController/g' ice-ration/routes/web.php
sed -i 's/ROLE_TRUCK_DRIVER/ROLE_TRUCK_MANAGER/g' ice-ration/routes/web.php
sed -i 's/prefix('\''driver'\'')/prefix('\''manager'\'')/g' ice-ration/routes/web.php
sed -i 's/name('\''driver\.'\'')/name('\''manager\.'\'')/g' ice-ration/routes/web.php

sed -i 's/driver->name/manager->name/g' ice-ration/resources/views/agent/deliveries.blade.php
sed -i 's/Unknown driver/Unknown manager/g' ice-ration/resources/views/agent/deliveries.blade.php

sed -i 's/\/driver\//\/manager\//g' ice-ration/tests/Feature/PanelSmokeTest.php
sed -i 's/truckDriver/truckManager/g' ice-ration/tests/Feature/PanelSmokeTest.php
sed -i 's/truck_driver/truck_manager/g' ice-ration/tests/Feature/PanelSmokeTest.php

