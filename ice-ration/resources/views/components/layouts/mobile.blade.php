<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <title>{{ $title ?? 'Panel' }} - {{ config('app.name') }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        body { font-size: 16px; }
        button, input, select, a.tap-target { min-height: 48px; min-width: 48px; }
    </style>
</head>
<body class="bg-slate-100 text-slate-900 min-h-screen flex flex-col">
    <header class="bg-slate-900 text-white px-4 py-4 flex items-center justify-between sticky top-0 z-10">
        <span class="font-bold text-base">❄ {{ $title ?? 'Ice Ration' }}</span>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="text-sm underline" style="min-height:48px;min-width:48px;">Sign out</button>
        </form>
    </header>

    <main class="flex-1 p-4 max-w-md w-full mx-auto">
        @if (session('status'))
            <div class="mb-4 rounded-xl bg-green-100 border border-green-300 text-green-800 text-base p-4 font-medium">
                {{ session('status') }}
            </div>
        @endif
        @if ($errors->any())
            <div class="mb-4 rounded-xl bg-red-100 border border-red-300 text-red-800 text-base p-4 font-medium">
                {{ $errors->first() }}
            </div>
        @endif

        {{ $slot }}
    </main>

    @isset($nav)
        <nav class="sticky bottom-0 bg-white border-t border-slate-200 flex">
            {{ $nav }}
        </nav>
    @endisset
</body>
</html>
