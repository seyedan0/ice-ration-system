<?php

use App\Http\Controllers\Admin\AnalyticsController;
use App\Http\Controllers\Admin\CitizenController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\InventoryController;
use App\Http\Controllers\Admin\StationController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Agent\DashboardController as AgentDashboardController;
use App\Http\Controllers\Agent\DeliveryController as AgentDeliveryController;
use App\Http\Controllers\Agent\TicketController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Manager\DeliveryController as ManagerDeliveryController;
use App\Http\Controllers\Manager\Truck\TruckController;
use App\Models\User;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
});

/*
|--------------------------------------------------------------------------
| Super Admin routes (desktop panel)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:' . User::ROLE_SUPER_ADMIN])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

        Route::get('analytics', [AnalyticsController::class, 'index'])->name('analytics');
        Route::get('analytics/export', [AnalyticsController::class, 'export'])->name('analytics.export');

        Route::resource('stations', StationController::class)->except(['show']);
        Route::patch('stations/{station}/toggle', [StationController::class, 'toggle'])->name('stations.toggle');

        Route::resource('users', UserController::class)->except(['show']);
        Route::patch('users/{user}/toggle', [UserController::class, 'toggle'])->name('users.toggle');

        Route::resource('citizens', CitizenController::class)->except(['show']);
        Route::patch('citizens/{citizen}/toggle', [CitizenController::class, 'toggle'])->name('citizens.toggle');
        Route::get('citizens/{citizen}/card', [CitizenController::class, 'card'])->name('citizens.card');
        Route::get('citizens/{citizen}/qr', [CitizenController::class, 'qr'])->name('citizens.qr');

        Route::get('inventory', [InventoryController::class, 'index'])->name('inventory');
        Route::post('inventory/{station}/adjust', [InventoryController::class, 'adjust'])->name('inventory.adjust');
    });

/*
|--------------------------------------------------------------------------
| Station Agent routes (mobile-first panel)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:' . User::ROLE_STATION_AGENT])
    ->prefix('agent')
    ->name('agent.')
    ->group(function () {
        Route::get('dashboard', [AgentDashboardController::class, 'index'])->name('dashboard');

        Route::get('validate', [TicketController::class, 'show'])->name('tickets.show');
        Route::post('tickets/validate', [TicketController::class, 'validateIdentifier'])->name('tickets.validate');
        Route::post('tickets/{ticket}/claim', [TicketController::class, 'claim'])->name('tickets.claim');

        Route::get('deliveries', [AgentDeliveryController::class, 'index'])->name('deliveries.index');
        Route::post('deliveries/{delivery}/confirm', [AgentDeliveryController::class, 'confirm'])->name('deliveries.confirm');
        Route::post('deliveries/{delivery}/reject', [AgentDeliveryController::class, 'reject'])->name('deliveries.reject');
    });

/*
|--------------------------------------------------------------------------
| Truck Driver routes (mobile-first panel)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:' . User::ROLE_TRUCK_MANAGER])
    ->prefix('manager')
    ->name('manager.')
    ->group(function () {
        Route::get('dashboard', [ManagerDeliveryController::class, 'create'])->name('dashboard');
        Route::post('deliveries', [ManagerDeliveryController::class, 'store'])->name('deliveries.store');
        Route::get('deliveries/history', [ManagerDeliveryController::class, 'history'])->name('deliveries.history');

        // Truck management for managers
        Route::resource('trucks', TruckController::class)->except(['show']);
    });
