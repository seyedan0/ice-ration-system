<x-layouts.mobile :title="$truck->exists ? 'Edit Truck' : 'Add Truck'">
    <a href="{{ route('manager.trucks.index') }}" class="text-sm text-slate-500 mb-3 inline-block">← Back to trucks</a>

    <div class="bg-white rounded-2xl shadow p-5">
        <form method="POST" action="{{ $truck->exists ? route('manager.trucks.update', $truck) : route('manager.trucks.store') }}" class="space-y-4">
            @csrf
            @if ($truck->exists) @method('PUT') @endif

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Plate Number</label>
                <input type="text" name="plate_number" value="{{ old('plate_number', $truck->plate_number) }}" required
                    placeholder="e.g. AB-1234-CD"
                    class="w-full rounded-xl border border-slate-300 px-4 py-3 text-lg" style="min-height:48px">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Capacity (ice blocks)</label>
                <input type="number" name="capacity" min="100" max="10000" value="{{ old('capacity', $truck->capacity ?? 1000) }}" required
                    placeholder="e.g. 1000"
                    class="w-full rounded-xl border border-slate-300 px-4 py-3 text-lg" style="min-height:48px">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Notes (optional)</label>
                <input type="text" name="notes" value="{{ old('notes', $truck->notes) }}"
                    placeholder="e.g. Refrigerated, Driver: John"
                    class="w-full rounded-xl border border-slate-300 px-4 py-3 text-base" style="min-height:48px">
            </div>
            <label class="flex items-center gap-2 text-sm text-slate-600">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $truck->is_active ?? true) ? 'checked' : '' }} class="rounded border-slate-300">
                Active
            </label>

            <button type="submit"
                class="tap-target w-full rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-lg font-bold py-4 mt-4">
                Save Truck
            </button>
        </form>
    </div>
</x-layouts.mobile>
