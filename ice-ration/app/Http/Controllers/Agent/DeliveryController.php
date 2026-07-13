<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\Delivery;
use App\Models\InventoryLog;
use App\Models\Station;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DeliveryController extends Controller
{
    public function index(Request $request)
    {
        $station = $request->user()->station;

        $deliveries = $station
            ? Delivery::query()
                ->where('station_id', $station->id)
                ->with('manager')
                ->orderByDesc('submitted_at')
                ->limit(30)
                ->get()
            : collect();

        return view('agent.deliveries', compact('station', 'deliveries'));
    }

    public function confirm(Request $request, Delivery $delivery): RedirectResponse
    {
        $agent = $request->user();

        if (! $agent->station_id || $delivery->station_id !== $agent->station_id) {
            abort(403, 'This delivery belongs to a different station.');
        }

        if (! $delivery->isPending()) {
            return back()->with('status', 'This delivery was already processed.');
        }

        DB::transaction(function () use ($delivery, $agent) {
            $lockedDelivery = Delivery::query()->lockForUpdate()->findOrFail($delivery->id);

            if (! $lockedDelivery->isPending()) {
                return;
            }

            $station = Station::query()->lockForUpdate()->findOrFail($lockedDelivery->station_id);

            $lockedDelivery->update([
                'status' => Delivery::STATUS_CONFIRMED,
                'confirmed_at' => now(),
                'confirmed_by_agent_id' => $agent->id,
            ]);

            $station->addStock($lockedDelivery->blocks_delivered, $agent->id, InventoryLog::TYPE_DELIVERY_IN, $lockedDelivery->id);
        });

        return back()->with('status', 'Delivery confirmed and stock updated.');
    }

    public function reject(Request $request, Delivery $delivery): RedirectResponse
    {
        $agent = $request->user();

        if (! $agent->station_id || $delivery->station_id !== $agent->station_id) {
            abort(403, 'This delivery belongs to a different station.');
        }

        if ($delivery->isPending()) {
            $delivery->update([
                'status' => Delivery::STATUS_REJECTED,
                'confirmed_at' => now(),
                'confirmed_by_agent_id' => $agent->id,
            ]);
        }

        return back()->with('status', 'Delivery rejected.');
    }
}
