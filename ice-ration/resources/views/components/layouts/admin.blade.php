<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Admin' }} - {{ config('app.name') }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
</head>
<body class="bg-slate-50 text-slate-800">
    <div class="flex min-h-screen">
        <aside class="w-64 bg-slate-900 text-slate-200 flex-shrink-0 hidden md:flex md:flex-col">
            <div class="px-6 py-5 text-white font-bold text-lg border-b border-slate-800">
                ❄ Ice Ration
            </div>
            <nav class="flex-1 px-3 py-4 space-y-1 text-sm">
                @php
                    $links = [
                        ['route' => 'admin.dashboard', 'label' => 'Dashboard', 'icon' => '🏠'],
                        ['route' => 'admin.analytics', 'label' => 'Analytics', 'icon' => '📊'],
                        ['route' => 'admin.stations.index', 'label' => 'Stations', 'icon' => '🏭'],
                        ['route' => 'admin.users.index', 'label' => 'Staff (Agents/Drivers)', 'icon' => '👥'],
                        ['route' => 'admin.citizens.index', 'label' => 'Citizens', 'icon' => '🪪'],
                        ['route' => 'admin.inventory', 'label' => 'Inventory', 'icon' => '📦'],
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
                    <button class="w-full text-left text-sm text-slate-300 hover:text-white">↩ Sign out ({{ auth()->user()?->name }})</button>
                </form>
            </div>
        </aside>

        <div class="flex-1 flex flex-col min-w-0">
            <header class="md:hidden bg-slate-900 text-white px-4 py-3 flex items-center justify-between">
                <span class="font-bold">❄ Ice Ration Admin</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="text-sm">Sign out</button>
                </form>
            </header>

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

                <h1 class="text-2xl font-bold text-slate-900 mb-6">{{ $title ?? 'Dashboard' }}</h1>

                {{ $slot }}
            </main>
        </div>
    </div>
</body>
</html>
