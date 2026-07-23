<x-layouts.admin :title="__('site.staff_agents_drivers')">
    <div class="flex items-center justify-between mb-4 flex-wrap gap-2">
        <form method="GET" class="flex gap-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('site.search_by_name') }}"
                class="rounded-lg border border-slate-300 px-3 py-2 text-sm w-56">
            <select name="role" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <option value="">{{ __('site.all_roles') }}</option>
                <option value="station_agent" @selected(request('role') === 'station_agent')>{{ __('site.role_station_agent') }}</option>
                <option value="truck_manager" @selected(request('role') === 'truck_manager')>{{ __('site.role_truck_manager') }}</option>
            </select>
            <button class="px-3 py-2 rounded-lg bg-slate-200 text-sm">{{ __('site.filter') }}</button>
        </form>
        <a href="{{ route('admin.users.create') }}" class="px-4 py-2 rounded-lg bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700">+ {{ __('site.new_staff_member') }}</a>
    </div>

    <div class="bg-white rounded-xl shadow overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-slate-500 text-left">
                <tr>
                    <th class="px-5 py-2">{{ __('site.name') }}</th>
                    <th class="px-5 py-2">{{ __('site.mobile') }}</th>
                    <th class="px-5 py-2">{{ __('site.role') }}</th>
                    <th class="px-5 py-2">{{ __('site.station') }}</th>
                    <th class="px-5 py-2">{{ __('site.status') }}</th>
                    <th class="px-5 py-2 text-right">{{ __('site.actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($users as $user)
                    <tr>
                        <td class="px-5 py-2.5 font-medium text-slate-800">{{ $user->name }}</td>
                        <td class="px-5 py-2.5">{{ $user->mobile }}</td>
                        <td class="px-5 py-2.5 capitalize">{{ str_replace('_', ' ', $user->role) }}</td>
                        <td class="px-5 py-2.5">{{ $user->station?->name ?? '—' }}</td>
                        <td class="px-5 py-2.5">
                            @if ($user->is_active)
                                <span class="inline-block px-2 py-0.5 rounded-full bg-green-100 text-green-700 text-xs">{{ __('site.active') }}</span>
                            @else
                                <span class="inline-block px-2 py-0.5 rounded-full bg-slate-200 text-slate-600 text-xs">{{ __('site.inactive') }}</span>
                            @endif
                        </td>
                        <td class="px-5 py-2.5 text-right space-x-2">
                            <a href="{{ route('admin.users.edit', $user) }}" class="text-blue-600 hover:underline">{{ __('site.edit') }}</a>
                            <form action="{{ route('admin.users.toggle', $user) }}" method="POST" class="inline">
                                @csrf @method('PATCH')
                                <button class="text-slate-500 hover:underline">{{ $user->is_active ? __('site.deactivate') : __('site.activate') }}</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-5 py-6 text-center text-slate-400">{{ __('site.no_staff_found') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $users->links() }}</div>
</x-layouts.admin>
