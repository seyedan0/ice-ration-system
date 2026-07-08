<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Citizen;
use App\Models\DailyTicket;
use App\Models\Delivery;
use App\Models\InventoryLog;
use App\Models\Station;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    public function index()
    {
        $today = today();

        $stations = Station::query()->orderBy('name')->get();

        // Per-station snapshot for today.
        $ticketsToday = DailyTicket::query()
            ->forToday()
            ->select('station_id', 'status', DB::raw('COUNT(*) as cnt'), DB::raw('SUM(allocated_blocks) as blocks'))
            ->groupBy('station_id', 'status')
            ->get()
            ->groupBy('station_id');

        $perStation = $stations->map(function (Station $station) use ($ticketsToday) {
            $rows = $ticketsToday->get($station->id, collect());

            $claimed = $rows->firstWhere('status', DailyTicket::STATUS_CLAIMED);
            $pending = $rows->firstWhere('status', DailyTicket::STATUS_PENDING);
            $expired = $rows->firstWhere('status', DailyTicket::STATUS_EXPIRED);

            return [
                'station' => $station,
                'current_stock' => $station->current_stock,
                'claimed_count' => (int) ($claimed->cnt ?? 0),
                'claimed_blocks' => (int) ($claimed->blocks ?? 0),
                'pending_count' => (int) ($pending->cnt ?? 0),
                'expired_count' => (int) ($expired->cnt ?? 0),
            ];
        });

        // System-wide totals for today.
        $totalStock = $stations->sum('current_stock');
        $totalBlocksOutToday = (int) DailyTicket::query()->forToday()->where('status', DailyTicket::STATUS_CLAIMED)->sum('allocated_blocks');
        $totalClaimedToday = DailyTicket::query()->forToday()->where('status', DailyTicket::STATUS_CLAIMED)->count();
        $totalPendingToday = DailyTicket::query()->forToday()->where('status', DailyTicket::STATUS_PENDING)->count();
        $totalExpiredToday = DailyTicket::query()->forToday()->where('status', DailyTicket::STATUS_EXPIRED)->count();
        $totalTicketsToday = $totalClaimedToday + $totalPendingToday + $totalExpiredToday;
        $claimRateToday = $totalTicketsToday > 0 ? round(($totalClaimedToday / $totalTicketsToday) * 100, 1) : 0;

        $activeCitizens = Citizen::query()->active()->count();
        $totalCitizens = Citizen::query()->count();

        $deliveriesToday = Delivery::query()->whereDate('submitted_at', $today)->count();
        $confirmedDeliveredBlocksToday = (int) Delivery::query()
            ->whereDate('confirmed_at', $today)
            ->where('status', Delivery::STATUS_CONFIRMED)
            ->sum('blocks_delivered');
        $pendingDeliveries = Delivery::query()->pending()->count();

        // Last 7 days consumption trend (ration_out is stored as a negative delta).
        $sevenDaysAgo = $today->copy()->subDays(6);
        $trendRaw = InventoryLog::query()
            ->where('change_type', InventoryLog::TYPE_RATION_OUT)
            ->whereDate('logged_at', '>=', $sevenDaysAgo)
            ->select(DB::raw('DATE(logged_at) as day'), DB::raw('SUM(ABS(blocks_delta)) as total'))
            ->groupBy('day')
            ->pluck('total', 'day');

        $trend = collect(range(0, 6))->map(function ($i) use ($sevenDaysAgo, $trendRaw) {
            $day = $sevenDaysAgo->copy()->addDays($i)->toDateString();

            return [
                'date' => $day,
                'blocks' => (int) ($trendRaw[$day] ?? 0),
            ];
        });

        // Top 5 stations by consumption today.
        $topStations = $perStation->sortByDesc('claimed_blocks')->take(5)->values();

        return view('admin.analytics.index', [
            'perStation' => $perStation,
            'totalStock' => $totalStock,
            'totalBlocksOutToday' => $totalBlocksOutToday,
            'totalClaimedToday' => $totalClaimedToday,
            'totalPendingToday' => $totalPendingToday,
            'totalExpiredToday' => $totalExpiredToday,
            'claimRateToday' => $claimRateToday,
            'activeCitizens' => $activeCitizens,
            'totalCitizens' => $totalCitizens,
            'deliveriesToday' => $deliveriesToday,
            'confirmedDeliveredBlocksToday' => $confirmedDeliveredBlocksToday,
            'pendingDeliveries' => $pendingDeliveries,
            'trend' => $trend,
            'topStations' => $topStations,
        ]);
    }

    /**
     * Export today's per-station distribution snapshot as CSV.
     */
    public function export(): Response
    {
        $today = today()->toDateString();

        $stations = Station::query()->orderBy('name')->get();

        $ticketsToday = DailyTicket::query()
            ->forToday()
            ->select('station_id', 'status', DB::raw('COUNT(*) as cnt'), DB::raw('SUM(allocated_blocks) as blocks'))
            ->groupBy('station_id', 'status')
            ->get()
            ->groupBy('station_id');

        $rows = ["Station,Current Stock,Claimed Today,Blocks Distributed,Pending Today,Expired Today"];

        foreach ($stations as $station) {
            $stationRows = $ticketsToday->get($station->id, collect());
            $claimed = $stationRows->firstWhere('status', DailyTicket::STATUS_CLAIMED);
            $pending = $stationRows->firstWhere('status', DailyTicket::STATUS_PENDING);
            $expired = $stationRows->firstWhere('status', DailyTicket::STATUS_EXPIRED);

            $rows[] = implode(',', [
                '"' . str_replace('"', '""', $station->name) . '"',
                $station->current_stock,
                (int) ($claimed->cnt ?? 0),
                (int) ($claimed->blocks ?? 0),
                (int) ($pending->cnt ?? 0),
                (int) ($expired->cnt ?? 0),
            ]);
        }

        $csv = implode("\n", $rows);

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"ice-ration-analytics-{$today}.csv\"",
        ]);
    }
}
