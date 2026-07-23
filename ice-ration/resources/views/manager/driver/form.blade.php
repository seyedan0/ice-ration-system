<x-layouts.mobile :title="$driver->exists ? __('site.edit_driver') : __('site.new_driver')">
    <div class="bg-white rounded-2xl shadow p-5 mb-4">
        <h2 class="font-bold text-lg text-slate-800 mb-4">
            {{ $driver->exists ? __('site.edit_driver') : __('site.new_driver') }}
        </h2>
        <form method="POST" action="{{ $driver->exists ? route('manager.drivers.update', $driver) : route('manager.drivers.store') }}" class="space-y-4">
            @csrf
            @if ($driver->exists) @method('PUT') @endif

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('site.full_name') }}</label>
                <input type="text" name="name" value="{{ old('name', $driver->name) }}" required
                       class="w-full rounded-xl border border-slate-300 px-4 py-3 text-lg" style="min-height:48px">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('site.mobile_number') }}</label>
                <input type="text" name="mobile" value="{{ old('mobile', $driver->mobile) }}" required
                       class="w-full rounded-xl border border-slate-300 px-4 py-3 text-lg" style="min-height:48px">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">
                    {{ __('site.password') }} {{ $driver->exists ? __('site.password_keep_blank') : '' }}
                </label>
                <input type="password" name="password"
                       class="w-full rounded-xl border border-slate-300 px-4 py-3 text-lg" style="min-height:48px">
            </div>

            <label class="flex items-center gap-2 text-sm text-slate-600">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $driver->is_active ?? true) ? 'checked' : '' }} class="rounded border-slate-300">
                {{ __('site.active') }}
            </label>



            <button type="submit"
                    class="tap-target w-full rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-lg font-bold py-4">
                {{ $driver->exists ? __('site.update_driver') : __('site.create_driver') }}
            </button>
        </form>

        <div class="mt-4 text-center">
            <a href="{{ route('manager.drivers.index') }}" class="text-sm text-slate-600 hover:text-slate-800">
                ← {{ __('site.back_to_drivers') }}
            </a>
        </div>
    </div>
</x-layouts.mobile>