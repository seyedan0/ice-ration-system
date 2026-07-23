<x-layouts.admin :title="__('site.citizens')">
    <div class="flex items-center justify-between mb-4">
        <form method="GET" class="flex gap-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('site.search_name_national_id_mobile_qr') }}"
                class="rounded-lg border border-slate-300 px-3 py-2 text-sm w-80">
            <button class="px-3 py-2 rounded-lg bg-slate-200 text-sm">{{ __('site.search') }}</button>
        </form>
        <a href="{{ route('admin.citizens.create') }}" class="px-4 py-2 rounded-lg bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700">+ {{ __('site.add_citizen') }}</a>
    </div>

    <div class="bg-white rounded-xl shadow overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-slate-500 text-left">
                <tr>
                    <th class="px-5 py-2">{{ __('site.citizen_name') }}</th>
                    <th class="px-5 py-2">{{ __('site.national_id') }}</th>
                    <th class="px-5 py-2">{{ __('site.mobile') }}</th>
                    <th class="px-5 py-2">{{ __('site.preferred_station') }}</th>
                    <th class="px-5 py-2">{{ __('site.daily_ration') }}</th>
                    <th class="px-5 py-2">{{ __('site.status') }}</th>
                    <th class="px-5 py-2 text-right">{{ __('site.actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($citizens as $citizen)
                    <tr>
                        <td class="px-5 py-2.5 font-medium text-slate-800">{{ $citizen->full_name }}</td>
                        <td class="px-5 py-2.5">{{ $citizen->national_id }}</td>
                        <td class="px-5 py-2.5">{{ $citizen->mobile }}</td>
                        <td class="px-5 py-2.5">{{ $citizen->preferredStation->name ?? '—' }}</td>
                        <td class="px-5 py-2.5">{{ $citizen->daily_ration }}</td>
                        <td class="px-5 py-2.5">
                            @if ($citizen->is_active)
                                <span class="inline-block px-2 py-0.5 rounded-full bg-green-100 text-green-700 text-xs">{{ __('site.active') }}</span>
                            @else
                                <span class="inline-block px-2 py-0.5 rounded-full bg-slate-200 text-slate-600 text-xs">{{ __('site.inactive') }}</span>
                            @endif
                        </td>
                        <td class="px-5 py-2.5 text-right space-x-2 whitespace-nowrap">
                            <a href="{{ route('admin.citizens.card', $citizen) }}" class="text-slate-600 hover:underline">{{ __('site.citizen_card') }}</a>
                            <a href="{{ route('admin.citizens.edit', $citizen) }}" class="text-blue-600 hover:underline">{{ __('site.edit') }}</a>
                            <form action="{{ route('admin.citizens.toggle', $citizen) }}" method="POST" class="inline">
                                @csrf @method('PATCH')
                                <button class="text-slate-500 hover:underline">{{ $citizen->is_active ? __('site.deactivate') : __('site.activate') }}</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-5 py-6 text-center text-slate-400">{{ __('site.no_citizens_found') }}</td></tr
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $citizens->links() }}</div>
</x-layouts.admin>
