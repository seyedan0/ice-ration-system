<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\DailyTicket;
use App\Models\Delivery;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $station = $request->user()->station;

        $pendingDeliveries = $station
            ? Delivery::query()->where('station_id', $station->id)->pending()->count()
            : 0;

        $claimedToday = $station
            ? DailyTicket::query()->where('station_id', $station->id)->forToday()->where('status', DailyTicket::STATUS_CLAIMED)->count()
            : 0;

        $pendingToday = $station
            ? DailyTicket::query()->where('station_id', $station->id)->forToday()->where('status', DailyTicket::STATUS_PENDING)->count()
            : 0;

        return view('agent.dashboard', compact('station', 'pendingDeliveries', 'claimedToday', 'pendingToday'));
    }
}
