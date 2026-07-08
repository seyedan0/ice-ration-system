<x-layouts.admin title="Stations">
    <div class="flex items-center justify-between mb-4">
        <form method="GET" class="flex gap-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search stations..."
                class="rounded-lg border border-slate-300 px-3 py-2 text-sm w-64">
            <button class="px-3 py-2 rounded-lg bg-slate-200 text-sm">Search</button>
        </form>
        <a href="{{ route('admin.stations.create') }}" class="px-4 py-2 rounded-lg bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700">+ New Station</a>
    </div>

    <div class="bg-white rounded-xl shadow overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-slate-500 text-left">
                <tr>
                    <th class="px-5 py-2">Name</th>
                    <th class="px-5 py-2">Address</th>
                    <th class="px-5 py-2">Current Stock</th>
                    <th class="px-5 py-2">Status</th>
                    <th class="px-5 py-2 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($stations as $station)
                    <tr>
                        <td class="px-5 py-2.5 font-medium text-slate-800">{{ $station->name }}</td>
                        <td class="px-5 py-2.5 text-slate-500">{{ $station->address ?? '—' }}</td>
                        <td class="px-5 py-2.5">{{ number_format($station->current_stock) }}</td>
                        <td class="px-5 py-2.5">
                            @if ($station->is_active)
                                <span class="inline-block px-2 py-0.5 rounded-full bg-green-100 text-green-700 text-xs">Active</span>
                            @else
                                <span class="inline-block px-2 py-0.5 rounded-full bg-slate-200 text-slate-600 text-xs">Inactive</span>
                            @endif
                        </td>
                        <td class="px-5 py-2.5 text-right space-x-2">
                            <a href="{{ route('admin.stations.edit', $station) }}" class="text-blue-600 hover:underline">Edit</a>
                            <form action="{{ route('admin.stations.toggle', $station) }}" method="POST" class="inline">
                                @csrf @method('PATCH')
                                <button class="text-slate-500 hover:underline">{{ $station->is_active ? 'Deactivate' : 'Activate' }}</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-5 py-6 text-center text-slate-400">No stations found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $stations->links() }}</div>
</x-layouts.admin>
