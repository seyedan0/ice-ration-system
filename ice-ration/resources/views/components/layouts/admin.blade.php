<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'fa' ? 'rtl' : 'ltr' }}">
<head>
    @include('components.layouts.partials.locale-head')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
</head>
<body class="bg-slate-50 text-slate-800">
    <div class="flex min-h-screen">
        <aside class="w-64 bg-slate-900 text-slate-200 flex-shrink-0 hidden md:flex md:flex-col">
            <div class="px-6 py-5 text-white font-bold text-lg border-b border-slate-800">
                ❄ {{ __('site.app_name') }}
            </div>
            <nav class="flex-1 px-3 py-4 space-y-1 text-sm">
                @php
                    $links = [
                        ['route' => 'admin.dashboard',  'label' => __('site.dashboard'),       'icon' => '🏠'],
                        ['route' => 'admin.analytics',  'label' => __('site.analytics'),       'icon' => '📊'],
                        ['route' => 'admin.stations.index', 'label' => __('site.stations'),     'icon' => '🏭'],
                        ['route' => 'admin.users.index', 'label' => __('site.staff_agents_drivers'), 'icon' => '👥'],
                        ['route' => 'admin.citizens.index', 'label' => __('site.citizens'),    'icon' => '🪪'],
                        ['route' => 'admin.inventory',   'label' => __('site.inventory'),       'icon' => '📦'],
                    ];
                @endphp
                @foreach ($links as $link)
                    <a href="{{ route($link['route']) }}"
                       class="flex items-center gap-3 rounded-lg px-3 py-2.5 hover:bg-slate-800 transition {{ request()->routeIs(explode('.', $link['route'])[0].'.'.explode('.', $link['route'])[1].'*') ? 'bg-slate-800 text-white' : '' }}">
                        <span>{{ $link['icon'] }}</span>
                        <span>{{ $link['label'] }}</span>
                    </a>
                @endforeach
            </nav>
            <div class="px-4 py-4 border-t border-slate-800">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="w-full text-left text-sm text-slate-300 hover:text-white">↩ {{ __('site.sign_out') }} ({{ auth()->user()?->name }})</button>
                </form>
            </div>
        </aside>

        <div class="flex-1 flex flex-col min-w-0">
            <header class="md:hidden bg-slate-900 text-white px-4 py-3 flex items-center justify-between">
                <span class="font-bold">❄ {{ __('site.app_name') }} — {{ __('site.welcome_admin') }}</span>
                <div class="flex items-center gap-2">
                    @include('components.language-switcher')
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="text-sm">{{ __('site.sign_out') }}</button>
                    </form>
                </div>
            </header>

            {{-- Desktop top bar with language switcher on right (LTR) / left (RTL) --}}
            <div class="hidden md:flex items-center justify-end px-8 py-3 border-b border-slate-200 bg-white">
                @include('components.language-switcher')
            </div>

            <main class="flex-1 p-4 md:p-8 max-w-7xl w-full mx-auto">
                @if (session('status'))
                    <div class="mb-4 rounded-lg bg-green-50 border border-green-200 text-green-700 text-sm p-3">
                        {{ session('status') }}
                    </div>
                @endif
                @if ($errors->any())
                    <div class="mb-4 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm p-3">
                        <ul class="list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <h1 class="text-2xl font-bold text-slate-900 mb-6">{{ $title ?? __('site.dashboard') }}</h1>

                {{ $slot }}
            </main>
        </div>
    </div>
</body>
</html>
