<x-layouts.admin :title="__('site.inventory_management')">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-8">
        @foreach ($stations as $station)
            <div class="bg-white rounded-xl shadow p-5">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="font-semibold text-slate-800">{{ $station->name }}</h3>
                    <span class="text-2xl font-bold text-blue-600">{{ number_format($station->current_stock) }}</span>
                </div>
                <form method="POST" action="{{ route('admin.inventory.adjust', $station) }}" class="flex gap-2">
                    @csrf
                    <input type="number" name="blocks_delta" placeholder="{{ __('site.adjustment_placeholder') }}" required
                        class="flex-1 rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <input type="text" name="reason" placeholder="{{ __('site.reason_optional') }}"
                        class="flex-1 rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <button class="px-4 py-2 rounded-lg bg-slate-800 text-white text-sm font-semibold hover:bg-slate-900">{{ __('site.adjust_inventory') }}</button>
                </form>
            </div>
        @endforeach
    </div>

    <div class="bg-white rounded-xl shadow overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100">
            <h2 class="font-semibold text-slate-800">{{ __('site.recent_inventory_activity') }}</h2>
        </div>
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-slate-500 text-left">
                <tr>
                    <th class="px-5 py-2">{{ __('site.station') }}</th>
                    <th class="px-5 py-2">{{ __('site.type') }}</th>
                    <th class="px-5 py-2">{{ __('site.change') }}</th>
                    <th class="px-5 py-2">{{ __('site.stock_after') }}</th>
                    <th class="px-5 py-2">{{ __('site.by') }}</th>
                    <th class="px-5 py-2">{{ __('site.when') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($recentLogs as $log)
                    <tr>
                        <td class="px-5 py-2.5">{{ $log->station->name ?? '—' }}</td>
                        <td class="px-5 py-2.5 capitalize">{{ str_replace('_', ' ', $log->change_type) }}</td>
                        <td class="px-5 py-2.5 font-semibold {{ $log->blocks_delta >= 0 ? 'text-green-600' : 'text-red-600' }}">
                            {{ $log->blocks_delta >= 0 ? '+' : '' }}{{ $log->blocks_delta }}</td>
                        <td class="px-5 py-2.5">{{ $log->stock_after }}</td>
                        <td class="px-5 py-2.5">{{ $log->performedBy->name ?? '—' }}</td>
                        <td class="px-5 py-2.5 text-slate-500">{{ $log->logged_at->diffForHumans() }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-5 py-6 text-center text-slate-400">{{ __('site.no_activity_yet') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-layouts.admin>
