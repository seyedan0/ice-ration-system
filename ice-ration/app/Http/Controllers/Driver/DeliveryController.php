<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Models\Delivery;
use App\Models\Station;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DeliveryController extends Controller
{
    public function create(Request $request)
    {
        $stations = Station::query()->active()->orderBy('name')->get();

        $recent = Delivery::query()
            ->where('driver_id', $request->user()->id)
            ->orderByDesc('submitted_at')
            ->limit(5)
            ->get();

        return view('driver.dashboard', compact('stations', 'recent'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'station_id' => ['required', 'exists:stations,id'],
            'blocks_delivered' => ['required', 'integer', 'min:1', 'max:100000'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        Delivery::create([
            'station_id' => $data['station_id'],
            'driver_id' => $request->user()->id,
            'blocks_delivered' => $data['blocks_delivered'],
            'notes' => $data['notes'] ?? null,
            'status' => Delivery::STATUS_PENDING,
        ]);

        return redirect()->route('driver.dashboard')
            ->with('status', 'Delivery reported. Waiting for agent confirmation.');
    }

    public function history(Request $request)
    {
        $deliveries = Delivery::query()
            ->where('driver_id', $request->user()->id)
            ->with('station')
            ->orderByDesc('submitted_at')
            ->limit(10)
            ->get();

        return view('driver.history', compact('deliveries'));
    }
}
