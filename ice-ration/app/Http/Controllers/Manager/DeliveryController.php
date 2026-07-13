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
        $trucks = Truck::query()->forManager($request->user()->id)->active()->orderBy('plate_number')->get();

        $recent = Delivery::query()
            ->where('manager_id', $request->user()->id)
            ->with(['station','truck'])
            ->orderByDesc('submitted_at')
            ->limit(5)
            ->get();

        return view('manager.dashboard', compact('stations', 'trucks', 'recent'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'station_id' => ['required', 'exists:stations,id'],
            'truck_id' => ['required', 'exists:trucks,id'],
            'blocks_delivered' => ['required', 'integer', 'min:1', 'max:100000'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        // Ensure the selected truck belongs to the authenticated manager
        $truck = \App\Models\Truck::where('id', $data['truck_id'])
            ->where('manager_id', $request->user()->id)
            ->first();

        if (! $truck) {
            abort(403, 'Selected truck does not belong to your fleet.');
        }

        Delivery::create([
            'station_id' => $data['station_id'],
            'manager_id' => $request->user()->id,
            'truck_id' => $truck->id,
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
