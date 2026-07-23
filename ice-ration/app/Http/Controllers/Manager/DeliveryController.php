<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Delivery;
use App\Models\Station;
use App\Models\Truck;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DeliveryController extends Controller
{
    public function create(Request $request)
    {
        $stations = Station::query()->active()->orderBy('name')->get();
        
        // Fetch drivers for this manager
        $drivers = $request->user()->drivers()->where('is_active', true)->orderBy('name')->get();
        
        \Log::info('Drivers for manager ' . $request->user()->id . ': ' . $drivers->pluck('name')->implode(', '));

        $recent = Delivery::query()
            ->where('manager_id', $request->user()->id)
            ->with(['station'])
            ->orderByDesc('submitted_at')
            ->limit(5)
            ->get();

        return view('manager.dashboard', compact('stations', 'drivers', 'recent'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'station_id' => ['required', 'exists:stations,id'],
            'driver_id' => ['required', 'exists:users,id'],
            'blocks_delivered' => ['required', 'integer', 'min:1', 'max:100000'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        // Ensure the selected driver belongs to the authenticated manager
        $driver = \App\Models\User::where('id', $data['driver_id'])
            ->where('manager_id', $request->user()->id)
            ->where('role', \App\Models\User::ROLE_TRUCK_DRIVER)
            ->first();

        if (! $driver) {
            abort(403, 'Selected driver does not belong to your fleet.');
        }
        


        Delivery::create([
            'station_id' => $data['station_id'],
            'manager_id' => $request->user()->id,
            'driver_id' => $driver->id,
            'blocks_delivered' => $data['blocks_delivered'],
            'notes' => $data['notes'] ?? null,
            'status' => Delivery::STATUS_PENDING,
        ]);

        return redirect()->route('manager.dashboard')
            ->with('status', 'Delivery reported. Waiting for agent confirmation.');
    }

    public function history(Request $request)
    {
        $deliveries = Delivery::query()
            ->where('manager_id', $request->user()->id)
            ->with(['station','truck'])
            ->orderByDesc('submitted_at')
            ->limit(10)
            ->get();

        return view('manager.history', compact('deliveries'));
    }
}
