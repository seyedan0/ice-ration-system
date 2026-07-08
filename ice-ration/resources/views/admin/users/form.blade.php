<x-layouts.admin :title="$user->exists ? 'Edit Staff Member' : 'New Staff Member'">
    <div class="bg-white rounded-xl shadow p-6 max-w-xl" x-data="{ role: '{{ old('role', $user->role ?? 'station_agent') }}' }">
        <form method="POST" action="{{ $user->exists ? route('admin.users.update', $user) : route('admin.users.store') }}" class="space-y-4">
            @csrf
            @if ($user->exists) @method('PUT') @endif

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Full Name</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Mobile Number</label>
                <input type="text" name="mobile" value="{{ old('mobile', $user->mobile) }}" required
                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Role</label>
                <select name="role" x-model="role" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <option value="station_agent">Station Agent</option>
                    <option value="truck_driver">Truck Driver</option>
                </select>
            </div>
            <div x-show="role === 'station_agent'">
                <label class="block text-sm font-medium text-slate-700 mb-1">Assigned Station</label>
                <select name="station_id" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <option value="">Select station...</option>
                    @foreach ($stations as $station)
                        <option value="{{ $station->id }}" @selected(old('station_id', $user->station_id) == $station->id)>{{ $station->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">
                    Password {{ $user->exists ? '(leave blank to keep current)' : '' }}
                </label>
                <input type="password" name="password" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </div>
            <label class="flex items-center gap-2 text-sm text-slate-600">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $user->is_active ?? true) ? 'checked' : '' }} class="rounded border-slate-300">
                Active
            </label>

            <div class="flex gap-2 pt-2">
                <button class="px-4 py-2 rounded-lg bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700">Save</button>
                <a href="{{ route('admin.users.index') }}" class="px-4 py-2 rounded-lg bg-slate-100 text-sm">Cancel</a>
            </div>
        </form>
    </div>
</x-layouts.admin>
