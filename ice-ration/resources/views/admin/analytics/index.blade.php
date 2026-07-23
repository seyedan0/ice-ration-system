<x-layouts.admin :title="__('site.analytics')">
    <div class="flex items-center justify-between mb-6">
        <p class="text-slate-500 text-sm">{{ __('site.live_analytics_for') }} {{ now()->toFormattedDateString() }}</p>
        <a href="{{ route('admin.analytics.export') }}" class="px-4 py-2 rounded-lg bg-slate-800 text-white text-sm font-semibold hover:bg-slate-900">⬇ {{ __('site.export_csv') }}</a>
    </div>

    {{-- KPI cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="bg-white rounded-xl shadow p-5">
            <p class="text-xs text-slate-500 uppercase tracking-wide">{{ __('site.total_stock') }}</p>
            <p class="text-3xl font-bold text-blue-600 mt-1">{{ number_format($totalStock) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow p-5">
            <p class="text-xs text-slate-500 uppercase tracking-wide">{{ __('site.blocks_out_today') }}</p>
            <p class="text-3xl font-bold text-green-600 mt-1">{{ number_format($totalBlocksOutToday) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow p-5">
            <p class="text-xs text-slate-500 uppercase tracking-wide">{{ __('site.claim_rate_today') }}</p>
            <p class="text-3xl font-bold text-slate-800 mt-1">{{ $claimRateToday }}%</p>
        </div>
        <div class="bg-white rounded-xl shadow p-5">
            <p class="text-xs text-slate-500 uppercase tracking-wide">{{ __('site.active_citizens') }}</p>
            <p class="text-3xl font-bold text-slate-800 mt-1">{{ number_format($activeCitizens) }} <span class="text-sm text-slate-400 font-normal">/ {{ $totalCitizens }}</span></p>
        </div>
        <div class="bg-white rounded-xl shadow p-5">
            <p class="text-xs text-slate-500 uppercase tracking-wide">{{ __('site.claimed_today') }}</p>
            <p class="text-2xl font-bold text-green-700 mt-1">{{ $totalClaimedToday }}</p>
        </div>
        <div class="bg-white rounded-xl shadow p-5">
            <p class="text-xs text-slate-500 uppercase tracking-wide">{{ __('site.pending_today') }}</p>
            <p class="text-2xl font-bold text-amber-600 mt-1">{{ $totalPendingToday }}</p>
        </div>
        <div class="bg-white rounded-xl shadow p-5">
            <p class="text-xs text-slate-500 uppercase tracking-wide">{{ __('site.expired_today') }}</p>
            <p class="text-2xl font-bold text-red-600 mt-1">{{ $totalExpiredToday }}</p>
        </div>
        <div class="bg-white rounded-xl shadow p-5">
            <p class="text-xs text-slate-500 uppercase tracking-wide">{{ __('site.deliveries_today') }}</p>
            <p class="text-2xl font-bold text-slate-800 mt-1">{{ $deliveriesToday }} <span class="text-sm text-slate-400 font-normal">({{ $pendingDeliveries }} {{ __('site.pending') }})</span></p>
        </div>
    </div>

    {{-- Charts --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow p-5">
            <h2 class="font-semibold text-slate-800 mb-3">{{ __('site.blocks_distributed_by_station_today') }}</h2>
            <canvas id="stationChart" height="220"></canvas>
        </div>
        <div class="bg-white rounded-xl shadow p-5">
            <h2 class="font-semibold text-slate-800 mb-3">{{ __('site.consumption_trend_last_7_days') }}</h2>
            <canvas id="trendChart" height="220"></canvas>
        </div>
    </div>

    {{-- Top consuming stations --}}
    <div class="bg-white rounded-xl shadow overflow-hidden mb-8">
        <div class="px-5 py-4 border-b border-slate-100">
            <h2 class="font-semibold text-slate-800">{{ __('site.top_stations_by_consumption_today') }}</h2>
        </div>
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-slate-500 text-left">
                <tr>
                    <th class="px-5 py-2">{{ __('site.station') }}</th>
                    <th class="px-5 py-2">{{ __('site.blocks_distributed') }}</th>
                    <th class="px-5 py-2">{{ __('site.citizens_served') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($topStations as $row)
                    <tr>
                        <td class="px-5 py-2.5 font-medium text-slate-800">{{ $row['station']->name }}</td>
                        <td class="px-5 py-2.5">{{ $row['claimed_blocks'] }}</td>
                        <td class="px-5 py-2.5">{{ $row['claimed_count'] }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="px-5 py-6 text-center text-slate-400">{{ __('site.no_distribution_activity_today') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Full per-station breakdown --}}
    <div class="bg-white rounded-xl shadow overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100">
            <h2 class="font-semibold text-slate-800">{{ __('site.per_station_breakdown_today') }}</h2>
        </div>
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-slate-500 text-left">
                <tr>
                    <th class="px-5 py-2">{{ __('site.station') }}</th>
                    <th class="px-5 py-2">{{ __('site.current_stock') }}</th>
                    <th class="px-5 py-2">{{ __('site.claimed') }}</th>
                    <th class="px-5 py-2">{{ __('site.pending') }}</th>
                    <th class="px-5 py-2">{{ __('site.expired') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach ($perStation as $row)
                    <tr>
                        <td class="px-5 py-2.5 font-medium text-slate-800">{{ $row['station']->name }}</td>
                        <td class="px-5 py-2.5">{{ number_format($row['current_stock']) }}</td>
                        <td class="px-5 py-2.5 text-green-700">{{ $row['claimed_count'] }} ({{ $row['claimed_blocks'] }} {{ __('site.blk') }})</td>
                        <td class="px-5 py-2.5 text-amber-600">{{ $row['pending_count'] }}</td>
                        <td class="px-5 py-2.5 text-red-600">{{ $row['expired_count'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <script>
        const stationLabels = @json($perStation->pluck('station.name'));
        const stationData = @json($perStation->pluck('claimed_blocks'));

        new Chart(document.getElementById('stationChart'), {
            type: 'bar',
            data: {
                labels: stationLabels,
                datasets: [{
                    label: {{ \json_encode(__('site.blocks_distributed')) }},
                    data: stationData,
                    backgroundColor: '#2563eb',
                    borderRadius: 6,
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true } }
            }
        });

        const trendLabels = @json($trend->pluck('date'));
        const trendData = @json($trend->pluck('blocks'));

        new Chart(document.getElementById('trendChart'), {
            type: 'line',
            data: {
                labels: trendLabels,
                datasets: [{
                    label: {{ \json_encode(__('site.blocks_consumed')) }},
                    data: trendData,
                    borderColor: '#16a34a',
                    backgroundColor: 'rgba(22,163,74,0.1)',
                    tension: 0.3,
                    fill: true,
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true } }
            }
        });
    </script>
</x-layouts.admin>
