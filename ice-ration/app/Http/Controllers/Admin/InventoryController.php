<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryLog;
use App\Models\Station;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    public function index()
    {
        $stations = Station::query()->orderBy('name')->get();

        $recentLogs = InventoryLog::query()
            ->with(['station', 'performedBy'])
            ->orderByDesc('logged_at')
            ->limit(30)
            ->get();

        return view('admin.inventory.index', compact('stations', 'recentLogs'));
    }

    public function adjust(Request $request, Station $station): RedirectResponse
    {
        $data = $request->validate([
            'blocks_delta' => ['required', 'integer', 'not_in:0'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($data, $station) {
            $station = Station::query()->lockForUpdate()->findOrFail($station->id);
            $delta = (int) $data['blocks_delta'];

            if ($delta > 0) {
                $station->addStock($delta, auth()->id(), InventoryLog::TYPE_MANUAL_ADJUSTMENT);
            } else {
                $station->deductStock(abs($delta), auth()->id(), null, InventoryLog::TYPE_MANUAL_ADJUSTMENT);
            }
        });

        return back()->with('status', 'Inventory adjusted successfully.');
    }
}
