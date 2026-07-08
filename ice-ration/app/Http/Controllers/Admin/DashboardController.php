<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DailyTicket;
use App\Models\Delivery;
use App\Models\Station;

class DashboardController extends Controller
{
    public function index()
    {
        $stations = Station::query()->orderBy('name')->get();

        $totalStock = $stations->sum('current_stock');
        $claimedToday = DailyTicket::query()->forToday()->where('status', DailyTicket::STATUS_CLAIMED)->count();
        $pendingToday = DailyTicket::query()->forToday()->where('status', DailyTicket::STATUS_PENDING)->count();
        $blocksOutToday = DailyTicket::query()->forToday()->where('status', DailyTicket::STATUS_CLAIMED)->sum('allocated_blocks');
        $pendingDeliveries = Delivery::query()->pending()->count();

        return view('admin.dashboard', [
            'stations' => $stations,
            'totalStock' => $totalStock,
            'claimedToday' => $claimedToday,
            'pendingToday' => $pendingToday,
            'blocksOutToday' => $blocksOutToday,
            'pendingDeliveries' => $pendingDeliveries,
        ]);
    }
}
