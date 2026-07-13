<x-layouts.mobile title="Delivery History">
    <a href="{{ route('manager.dashboard') }}" class="text-sm text-slate-500 mb-3 inline-block">← Back to dashboard</a>

    <div class="space-y-3">
        @forelse ($deliveries as $delivery)
            <div class="bg-white rounded-2xl shadow p-4">
                <div class="flex items-center justify-between mb-1">
                    <p class="font-bold text-slate-800">{{ $delivery->station->name ?? '—' }}</p>
                    @if ($delivery->status === 'pending')
                        <span class="px-2 py-0.5 rounded-full bg-amber-100 text-amber-700 text-xs font-semibold">Pending</span>
                    @elseif ($delivery->status === 'confirmed')
                        <span class="px-2 py-0.5 rounded-full bg-green-100 text-green-700 text-xs font-semibold">Confirmed</span>
                    @else
                        <span class="px-2 py-0.5 rounded-full bg-red-100 text-red-700 text-xs font-semibold">Rejected</span>
                    @endif
                </div>
                <p class="text-2xl font-extrabold text-blue-600">{{ $delivery->blocks_delivered }} <span class="text-sm font-normal text-slate-400">blocks</span></p>
                <p class="text-xs text-slate-400 mt-1">Submitted {{ $delivery->submitted_at->diffForHumans() }}</p>
            </div>
        @empty
            <div class="bg-white rounded-2xl shadow p-6 text-center text-slate-400">
                No deliveries reported yet.
            </div>
        @endforelse
    </div>
</x-layouts.mobile>
