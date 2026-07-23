<x-layouts.admin :title="$station->exists ? __('site.edit_station') : __('site.new_station')">
    <div class="bg-white rounded-xl shadow p-6 max-w-xl">
        <form method="POST" action="{{ $station->exists ? route('admin.stations.update', $station) : route('admin.stations.store') }}" class="space-y-4">
            @csrf
            @if ($station->exists) @method('PUT') @endif

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('site.name') }}</label>
                <input type="text" name="name" value="{{ old('name', $station->name) }}" required
                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('site.address') }}</label>
                <textarea name="address" rows="2" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">{{ old('address', $station->address) }}</textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('site.current_stock_ice_blocks') }}</label>
                <input type="number" name="current_stock" min="0" value="{{ old('current_stock', $station->current_stock ?? 0) }}" required
                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </div>
            <label class="flex items-center gap-2 text-sm text-slate-600">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $station->is_active ?? true) ? 'checked' : '' }} class="rounded border-slate-300">
                {{ __('site.active') }}
            </label>

            <div class="flex gap-2 pt-2">
                <button class="px-4 py-2 rounded-lg bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700">{{ __('site.save') }}</button>
                <a href="{{ route('admin.stations.index') }}" class="px-4 py-2 rounded-lg bg-slate-100 text-sm">{{ __('site.cancel') }}</a>
            </div>
        </form>
    </div>
</x-layouts.admin>
