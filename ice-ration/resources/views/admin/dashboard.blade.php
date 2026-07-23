<x-layouts.admin :title="__('site.welcome_admin')">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="bg-white rounded-xl shadow p-5">
            <p class="text-sm text-slate-500">{{ __('site.total_stock') }}</p>
            <p class="text-3xl font-bold text-blue-600 mt-1">{{ number_format($totalStock) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow p-5">
            <p class="text-sm text-slate-500">{{ __('site.blocks_distributed_today') }}</p>
            <p class="text-3xl font-bold text-green-600 mt-1">{{ number_format($blocksOutToday) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow p-5">
            <p class="text-sm text-slate-500">{{ __('site.claimed_pending_today') }}</p>
            <p class="text-3xl font-bold text-slate-800 mt-1">{{ $claimedToday }} / {{ $pendingToday }}</p>
        </div>
        <div class="bg-white rounded-xl shadow p-5">
            <p class="text-sm text-slate-500">{{ __('site.pending_deliveries') }}</p>
            <p class="text-3xl font-bold text-amber-600 mt-1">{{ $pendingDeliveries }}</p>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
            <h2 class="font-semibold text-slate-800">{{ __('site.stations_overview') }}</h2>
            <a href="{{ route('admin.analytics') }}" class="text-sm text-blue-600 hover:underline">{{ __('site.view_full_analytics') }}</a>
        </div>
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-slate-500 text-left">
                <tr>
                    <th class="px-5 py-2">{{ __('site.station') }}</th>
                    <th class="px-5 py-2">{{ __('site.current_stock') }}</th>
                    <th class="px-5 py-2">{{ __('site.status') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach ($stations as $station)
                    <tr>
                        <td class="px-5 py-2.5 font-medium text-slate-800">{{ $station->name }}</td>
                        <td class="px-5 py-2.5">{{ number_format($station->current_stock) }}</td>
                        <td class="px-5 py-2.5">
                            @if ($station->is_active)
                                <span class="inline-block px-2 py-0.5 rounded-full bg-green-100 text-green-700 text-xs">{{ __('site.active') }}</span>
                            @else
                                <span class="inline-block px-2 py-0.5 rounded-full bg-slate-200 text-slate-600 text-xs">{{ __('site.inactive') }}</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</x-layouts.admin>
