<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'fa' ? 'rtl' : 'ltr' }}">
<head>
    @include('components.layouts.partials.locale-head')
</head>
<body class="bg-slate-100 min-h-screen flex items-center justify-center p-4">
    {{-- Language switcher (top-right in LTR / top-left in RTL) --}}
    <div class="fixed top-4 {{ app()->getLocale() === 'fa' ? 'left-4' : 'right-4' }} z-50">
        @include('components.language-switcher')
    </div>

    {{ $slot }}
</body>
</html>
