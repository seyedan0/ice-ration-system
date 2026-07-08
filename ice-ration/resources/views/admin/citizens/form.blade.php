<x-layouts.admin :title="$citizen->exists ? 'Edit Citizen' : 'Register Citizen'">
    <div class="bg-white rounded-xl shadow p-6 max-w-xl">
        <form method="POST" action="{{ $citizen->exists ? route('admin.citizens.update', $citizen) : route('admin.citizens.store') }}" class="space-y-4">
            @csrf
            @if ($citizen->exists) @method('PUT') @endif

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Full Name</label>
                <input type="text" name="full_name" value="{{ old('full_name', $citizen->full_name) }}" required
                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">National ID (10 digits)</label>
                <input type="text" name="national_id" value="{{ old('national_id', $citizen->national_id) }}" required
                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Mobile Number</label>
                <input type="text" name="mobile" value="{{ old('mobile', $citizen->mobile) }}" required
                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Daily Ration (ice blocks)</label>
                <input type="number" name="daily_ration" min="1" max="50" value="{{ old('daily_ration', $citizen->daily_ration ?? 1) }}" required
                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Preferred Station</label>
                <select name="preferred_station_id" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <option value="">Select station...</option>
                    @foreach ($stations as $station)
                        <option value="{{ $station->id }}" @selected(old('preferred_station_id', $citizen->preferred_station_id) == $station->id)>{{ $station->name }}</option>
                    @endforeach
                </select>
            </div>
            <label class="flex items-center gap-2 text-sm text-slate-600">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $citizen->is_active ?? true) ? 'checked' : '' }} class="rounded border-slate-300">
                Active
            </label>

            @if ($citizen->exists)
                <p class="text-xs text-slate-400">QR Code: {{ $citizen->qr_code }}</p>
            @endif

            <div class="flex gap-2 pt-2">
                <button class="px-4 py-2 rounded-lg bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700">Save</button>
                <a href="{{ route('admin.citizens.index') }}" class="px-4 py-2 rounded-lg bg-slate-100 text-sm">Cancel</a>
            </div>
        </form>
    </div>
</x-layouts.admin>
