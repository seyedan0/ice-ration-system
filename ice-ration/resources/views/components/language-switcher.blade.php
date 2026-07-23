{{-- Language switcher — works on both desktop and mobile, in any layout --}}
@php
    $current = app()->getLocale();
    $isFa = $current === 'fa';
@endphp
<div class="inline-flex items-center gap-1 rounded-lg bg-slate-800/70 p-1 text-xs">
    <a href="{{ route('language.switch', 'fa') }}"
       class="px-2.5 py-1.5 rounded-md transition font-bold {{ $isFa ? 'bg-white text-slate-900' : 'text-slate-200 hover:text-white' }}"
       style="min-height:32px;">
        فارسی
    </a>
    <a href="{{ route('language.switch', 'en') }}"
       class="px-2.5 py-1.5 rounded-md transition font-semibold {{ ! $isFa ? 'bg-white text-slate-900' : 'text-slate-200 hover:text-white' }}"
       style="min-height:32px;">
        EN
    </a>
</div>
