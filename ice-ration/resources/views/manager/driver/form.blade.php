<x-layouts.mobile title="{{ $driver->exists ? 'Edit Driver' : 'New Driver' }}">
    <div class="bg-white rounded-2xl shadow p-5 mb-4">
        <h2 class="font-bold text-lg text-slate-800 mb-4">
            {{ $driver->exists ? 'Edit Driver' : 'New Driver' }}
        </h2>
        <form method="POST" action="{{ $driver->exists ? route('manager.drivers.update', $driver) : route('manager.drivers.store') }}" class="space-y-4">
            @csrf
            @if ($driver->exists) @method('PUT') @endif

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Full Name</label>
                <input type="text" name="name" value="{{ old('name', $driver->name) }}" required
                       class="w-full rounded-xl border border-slate-300 px-4 py-3 text-lg" style="min-height:48px">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Mobile Number</label>
                <input type="text" name="mobile" value="{{ old('mobile', $driver->mobile) }}" required
                       class="w-full rounded-xl border border-slate-300 px-4 py-3 text-lg" style="min-height:48px">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">
                    Password {{ $driver->exists ? '(leave blank to keep current)' : '' }}
                </label>
                <input type="password" name="password"
                       class="w-full rounded-xl border border-slate-300 px-4 py-3 text-lg" style="min-height:48px">
            </div>

            <label class="flex items-center gap-2 text-sm text-slate-600">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $driver->is_active ?? true) ? 'checked' : '' }} class="rounded border-slate-300">
                Active
            </label>

            <button type="submit"
                    class="tap-target w-full rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-lg font-bold py-4">
                {{ $driver->exists ? 'Update Driver' : 'Create Driver' }}
            </button>
        </form>

        <div class="mt-4 text-center">
            <a href="{{ route('manager.drivers.index') }}" class="text-sm text-slate-600 hover:text-slate-800">
                ← Back to Drivers
            </a>
        </div>
    </div>
</x-layouts.mobile>