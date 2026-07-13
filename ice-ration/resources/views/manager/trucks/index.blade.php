<x-layouts.mobile title="My Trucks">
    <a href="{{ route('manager.dashboard') }}" class="text-sm text-slate-500 mb-3 inline-block">← Back to dashboard</a>

    <div class="flex justify-end mb-4">
        <a href="{{ route('manager.trucks.create') }}"
           class="tap-target rounded-xl bg-blue-600 text-white font-semibold py-3 px-5">
            + Add Truck
        </a>
    </div>

    <div class="space-y-3">
        @forelse ($trucks as $truck)
            <div class="bg-white rounded-2xl shadow p-4">
                <div class="flex items-center justify-between mb-2">
                    <p class="font-bold text-slate-800">{{ $truck->plate_number }}</p>
                    @if ($truck->is_active)
                        <span class="px-2 py-0.5 rounded-full bg-green-100 text-green-700 text-xs font-semibold">Active</span>
                    @else
                        <span class="px-2 py-0.5 rounded-full bg-slate-200 text-slate-600 text-xs font-semibold">Inactive</span>
                    @endif
                </div>
                <p class="text-lg font-bold text-blue-600">{{ $truck->capacity }} <span class="text-sm font-normal text-slate-400">blocks max</span></p>
                @if ($truck->notes)
                    <p class="text-sm text-slate-500 mt-1">{{ $truck->notes }}</p>
                @endif

                <div class="grid grid-cols-2 gap-3 mt-3">
                    <a href="{{ route('manager.trucks.edit', $truck) }}"
                       class="tap-target text-center rounded-xl bg-slate-200 text-slate-700 font-semibold py-2">
                        Edit
                    </a>
                    <form method="POST" action="{{ route('manager.trucks.destroy', $truck) }}">
                        @csrf @method('DELETE')
                        <button class="tap-target w-full rounded-xl text-white font-semibold py-2"
                                style="background-color:#dc2626">
                            Deactivate
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="bg-white rounded-2xl shadow p-6 text-center text-slate-400">
                No trucks registered yet.
            </div>
        @endforelse
    </div>
</x-layouts.mobile>
