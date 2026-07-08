<x-layouts.mobile title="Agent Dashboard">
    <div class="text-center bg-white rounded-2xl shadow p-6 mb-4">
        <p class="text-sm text-slate-500">{{ $station->name ?? 'No station assigned' }}</p>
        <p class="text-5xl font-extrabold text-blue-600 my-3">{{ number_format($station->current_stock ?? 0) }}</p>
        <p class="text-sm text-slate-400">ice blocks in stock</p>
    </div>

    <div class="grid grid-cols-2 gap-3 mb-4">
        <div class="bg-white rounded-xl shadow p-4 text-center">
            <p class="text-2xl font-bold text-green-600">{{ $claimedToday }}</p>
            <p class="text-xs text-slate-500">Claimed Today</p>
        </div>
        <div class="bg-white rounded-xl shadow p-4 text-center">
            <p class="text-2xl font-bold text-amber-600">{{ $pendingToday }}</p>
            <p class="text-xs text-slate-500">Pending Today</p>
        </div>
    </div>

    <div class="space-y-3">
        <a href="{{ route('agent.tickets.show') }}"
           class="tap-target flex items-center justify-center gap-2 w-full rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-lg font-bold py-4 shadow">
            🔍 Validate Citizen
        </a>
        <a href="{{ route('agent.deliveries.index') }}"
           class="tap-target relative flex items-center justify-center gap-2 w-full rounded-xl bg-slate-800 hover:bg-slate-900 text-white text-lg font-bold py-4 shadow">
            🚚 Confirm Delivery
            @if ($pendingDeliveries > 0)
                <span class="absolute -top-2 -right-2 bg-red-600 text-white text-xs w-7 h-7 flex items-center justify-center rounded-full border-2 border-white">
                    {{ $pendingDeliveries }}
                </span>
            @endif
        </a>
    </div>
</x-layouts.mobile>
