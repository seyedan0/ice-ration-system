<x-layouts.mobile title="Report Delivery">
    <div class="bg-white rounded-2xl shadow p-5 mb-4">
        <h2 class="font-bold text-lg text-slate-800 mb-4">New Delivery</h2>
        <form method="POST" action="{{ route('driver.deliveries.store') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">1. Select Station</label>
                <select name="station_id" required class="w-full rounded-xl border border-slate-300 px-4 py-3 text-lg" style="min-height:48px">
                    <option value="">Choose a station...</option>
                    @foreach ($stations as $station)
                        <option value="{{ $station->id }}" @selected(old('station_id') == $station->id)>{{ $station->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">2. Number of Ice Blocks</label>
                <input type="number" name="blocks_delivered" min="1" required inputmode="numeric"
                    value="{{ old('blocks_delivered') }}"
                    placeholder="e.g. 200"
                    class="w-full rounded-xl border border-slate-300 px-4 py-3 text-2xl font-bold" style="min-height:48px">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Notes (optional)</label>
                <input type="text" name="notes" value="{{ old('notes') }}"
                    class="w-full rounded-xl border border-slate-300 px-4 py-3 text-base" style="min-height:48px">
            </div>
            <button type="submit"
                class="tap-target w-full rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-lg font-bold py-4">
                3. Submit Delivery
            </button>
        </form>
    </div>

    <a href="{{ route('driver.deliveries.history') }}" class="tap-target block text-center w-full rounded-xl bg-slate-200 text-slate-700 font-semibold py-3">
        View My Delivery History
    </a>

    @if ($recent->isNotEmpty())
        <div class="mt-6">
            <h3 class="text-sm font-semibold text-slate-500 mb-2">Recent Deliveries</h3>
            <div class="space-y-2">
                @foreach ($recent as $delivery)
                    <div class="bg-white rounded-xl shadow p-3 flex items-center justify-between text-sm">
                        <div>
                            <p class="font-semibold text-slate-800">{{ $delivery->station->name ?? '—' }}</p>
                            <p class="text-slate-400 text-xs">{{ $delivery->submitted_at->diffForHumans() }}</p>
                        </div>
                        <div class="text-right">
                            <p class="font-bold">{{ $delivery->blocks_delivered }} blk</p>
                            @if ($delivery->status === 'pending')
                                <span class="text-amber-600 text-xs">Pending</span>
                            @elseif ($delivery->status === 'confirmed')
                                <span class="text-green-600 text-xs">Confirmed</span>
                            @else
                                <span class="text-red-600 text-xs">Rejected</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</x-layouts.mobile>
