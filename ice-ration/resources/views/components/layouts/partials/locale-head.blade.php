@php
    $isFa = app()->getLocale() === 'fa';
    $dir = $isFa ? 'rtl' : 'ltr';
@endphp
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>{{ $title ?? config('app.name') }}</title>

@if ($isFa)
    {{-- Vazirmatn: a clean modern Persian font --}}
    <link rel="preconnect" href="https://cdn.jsdelivr.net">
    <link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css" rel="stylesheet" type="text/css">
    <style>
        html, body, * { font-family: 'Vazirmatn', 'Tahoma', system-ui, sans-serif !important; }
        html { direction: rtl; }
        body { text-align: right; }
    </style>
@endif
<script src="https://cdn.tailwindcss.com"></script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
