<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Citizen Card - {{ $citizen->full_name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; }
        }
    </style>
</head>
<body class="bg-slate-100 min-h-screen flex flex-col items-center py-10 gap-4">
    <div class="no-print flex gap-3">
        <a href="{{ route('admin.citizens.index') }}" class="px-4 py-2 rounded-lg bg-slate-200 text-sm">← Back to list</a>
        <button onclick="window.print()" class="px-4 py-2 rounded-lg bg-blue-600 text-white text-sm font-semibold">Print Card</button>
    </div>

    <div class="w-96 bg-white rounded-2xl shadow-lg border-4 border-blue-600 p-6 text-center">
        <p class="text-xs uppercase tracking-wide text-slate-400">Ice Ration System</p>
        <h1 class="text-lg font-bold text-slate-800 mt-1">{{ $citizen->full_name }}</h1>
        <p class="text-sm text-slate-500">National ID: {{ $citizen->national_id }}</p>
        <p class="text-sm text-slate-500 mb-4">Mobile: {{ $citizen->mobile }}</p>

        <img src="{{ route('admin.citizens.qr', $citizen) }}" alt="QR Code" class="mx-auto w-48 h-48">

        <p class="text-xs text-slate-400 mt-3 break-all">{{ $citizen->qr_code }}</p>

        <div class="mt-4 pt-4 border-t border-slate-200 text-sm text-slate-600">
            <p>Daily Ration: <strong>{{ $citizen->daily_ration }} block(s)</strong></p>
            <p>Home Station: <strong>{{ $citizen->preferredStation->name ?? '—' }}</strong></p>
        </div>
    </div>
</body>
</html>
