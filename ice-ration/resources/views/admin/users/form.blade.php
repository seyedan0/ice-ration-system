<x-layouts.admin :title="$user->exists ? __('site.edit_staff_member') : __('site.new_staff_member')">
    <div class="bg-white rounded-xl shadow p-6 max-w-xl" x-data="{ role: '{{ old('role', $user->role ?? 'station_agent') }}' }">
        <form method="POST" action="{{ $user->exists ? route('admin.users.update', $user) : route('admin.users.store') }}" class="space-y-4">
            @csrf
            @if ($user->exists) @method('PUT') @endif

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('site.full_name') }}</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('site.mobile_number') }}</label>
                <input type="text" name="mobile" value="{{ old('mobile', $user->mobile) }}" required
                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('site.role') }}</label>
                <select name="role" x-model="role" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                                    <option value="station_agent">{{ __('site.role_station_agent') }}</option>
                                    <option value="truck_manager">{{ __('site.role_truck_manager') }}</option>
                                    <option value="truck_driver">{{ __('site.role_truck_driver') }}</option>
                                </select>
            </div>
            <div x-show="role === 'station_agent'">
                <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('site.assigned_station') }}</label>
                <select name="station_id" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <option value="">{{ __('site.select_station') }}</option>
                    @foreach ($stations as $station)
                        <option value="{{ $station->id }}" @selected(old('station_id', $user->station_id) == $station->id)>{{ $station->name }}</option>
                    @endforeach
                </select>
            </div>
            <div x-show="role === 'truck_driver'">
                <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('site.assigned_manager') }}</label>
                <select name="manager_id" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <option value="">{{ __('site.select_manager') }}</option>
                    @foreach ($managers as $manager)
                        <option value="{{ $manager->id }}" @selected(old('manager_id', $user->manager_id) == $manager->id)>{{ $manager->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">
                    {{ __('site.password') }} {{ $user->exists ? __('site.password_keep_blank') : '' }}
                </label>
                <input type="password" name="password" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </div>
            <label class="flex items-center gap-2 text-sm text-slate-600">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $user->is_active ?? true) ? 'checked' : '' }} class="rounded border-slate-300">
                {{ __('site.active') }}
            </label>

            <div class="flex gap-2 pt-2">
                <button class="px-4 py-2 rounded-lg bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700">{{ __('site.save') }}</button>
                <a href="{{ route('admin.users.index') }}" class="px-4 py-2 rounded-lg bg-slate-100 text-sm">{{ __('site.cancel') }}</a>
            </div>
        </form>
    </div>
</x-layouts.admin>
