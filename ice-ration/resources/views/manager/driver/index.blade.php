<x-layouts.mobile title="My Drivers">
    <div class="bg-white rounded-2xl shadow p-5 mb-4">
        <div class="flex justify-between items-center mb-4">
            <h2 class="font-bold text-lg text-slate-800">My Drivers</h2>
            <a href="{{ route('manager.drivers.create') }}" 
               class="tap-target inline-block bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg">
                Add Driver
            </a>
        </div>

        @if ($drivers->isNotEmpty())
            <div class="divide-y divide-slate-200">
                @foreach ($drivers as $driver)
                    <div class="py-4 flex items-center justify-between">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10">
                                    <img class="h-full w-full rounded-full object-cover" 
                                         src="https://ui-avatars.com/api/?name={{ urlencode($driver->name )}}&background=random"
                                         alt="{{ $driver->name }}">
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-slate-900">{{ $driver->name }}</p>
                                    <p class="text-sm text-slate-500">{{ $driver->mobile }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="flex space-x-3">
                            <a href="{{ route('manager.drivers.edit', $driver) }}" 
                               class="tap-target text-sm font-medium text-indigo-600 hover:text-indigo-900">
                                Edit
                            </a>
                            <form action="{{ route('manager.drivers.destroy', $driver) }}" method="POST" 
                                  onsubmit="return confirm('Are you sure you want to deactivate this driver?');"
                                  style="display: inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" 
                                        class="tap-target text-sm font-medium text-red-600 hover:text-red-900">
                                    Deactivate
                                </button>
                            </form>
                        </div>
                    </div>
                    @if (!$loop->last)
                        <div class="border-t border-slate-200"></div>
                    @endif
                @endforeach
            </div>
        @else
            <div class="text-center py-8">
                <p class="text-slate-500">You haven't added any drivers yet.</p>
                <a href="{{ route('manager.drivers.create') }}" 
                   class="mt-4 inline-block text-sm font-medium text-indigo-600 hover:text-indigo-900">
                    Add Your First Driver
                </a>
            </div>
        @endif
    </div>
</x-layouts.mobile>