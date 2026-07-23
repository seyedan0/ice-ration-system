<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'fa' ? 'rtl' : 'ltr' }}">
<head>
    @include('components.layouts.partials.locale-head')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
    <style>
        body { font-size: 16px; }
        button, input, select, a.tap-target { min-height: 48px; min-width: 48px; }
    </style>
</head>
<body class="bg-slate-100 text-slate-900 min-h-screen flex flex-col">
    <header class="bg-slate-900 text-white px-4 py-4 flex items-center justify-between sticky top-0 z-10">
        <span class="font-bold text-base">❄ {{ $title ?? __('site.app_name') }}</span>
        <div class="flex items-center gap-3">
            @include('components.language-switcher')
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="text-sm underline" style="min-height:48px;min-width:48px;">{{ __('site.sign_out') }}</button>
            </form>
        </div>
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
