<x-layouts.mobile :title="__('site.confirm_delivery')">
    <a href="{{ route('agent.dashboard') }}" class="text-sm text-slate-500 mb-3 inline-block">← {{ __('site.back_to_dashboard') }}</a>

    <div class="space-y-3">
        @forelse ($deliveries as $delivery)
            <div class="bg-white rounded-2xl shadow p-4">
                <div class="flex items-center justify-between mb-2">
                    <p class="font-bold text-slate-800">
                        {{ $delivery->manager->name ?? __('site.unknown_manager') }}
                                                @if($delivery->truck)
                                                    <span class="text-sm font-normal text-slate-500 ml-2">({{ $delivery->truck->plate_number }})</span
                                                @endif
                    </p>
                    @if ($delivery->status === 'pending')
                        <span class="px-2 py-0.5 rounded-full bg-amber-100 text-amber-700 text-xs font-semibold">{{ __('site.pending') }}</span
                    @elseif ($delivery->status === 'confirmed')
                        <span class="px-2 py-0.5 rounded-full bg-green-100 text-green-700 text-xs font-semibold">{{ __('site.confirmed') }}</span
                    @else
                        <span class="px-2 py-0.5 rounded-full bg-red-100 text-red-700 text-xs font-semibold">{{ __('site.rejected') }}</span
                    @endif
                </div>
                <p class="text-3xl font-extrabold text-blue-600">{{ $delivery->blocks_delivered }} <span class="text-base font-normal text-slate-400">{{ __('site.blocks') }}</span></p>
                <p class="text-xs text-slate-400 mt-1">{{ __('site.submitted') }} {{ $delivery->submitted_at->diffForHumans() }}</p>

                @if ($delivery->isPending())
                    <div class="grid grid-cols-2 gap-3 mt-3">
                        <form method="POST" action="{{ route('agent.deliveries.reject', $delivery) }}">
                            @csrf
                            <button class="tap-target w-full rounded-xl text-white font-semibold" style="background-color:#dc2626"><span></span> {{ __('site.reject') }}</button>
                        </form>
                        <form method="POST" action="{{ route('agent.deliveries.confirm', $delivery) }}">
                            @csrf
                            <button class="tap-target w-full rounded-xl text-white font-semibold" style="background-color:#16a34a"><span></span> {{ __('site.confirm') }}</button>
                        </form>
                    </div>
                @endif
            </div>
        @empty
            <div class="bg-white rounded-2xl shadow p-6 text-center text-slate-400">
                {{ __('site.no_deliveries_yet') }}
            </div>
        @endforelse
    </div>
</x-layouts.mobile>
