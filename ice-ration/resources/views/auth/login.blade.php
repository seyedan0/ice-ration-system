<x-layouts.guest :title="__('site.login') . ' - ' . __('site.system_title')">
    <div class="w-full max-w-sm bg-white rounded-2xl shadow-lg p-8">
        <div class="text-center mb-8">
            <div class="mx-auto w-14 h-14 rounded-full bg-blue-600 text-white flex items-center justify-center text-2xl font-bold">
                ❄
            </div>
            <h1 class="mt-4 text-xl font-bold text-slate-800">{{ __('site.app_name') }}</h1>
            <p class="text-sm text-slate-500">{{ __('site.login_subtitle') }}</p>
        </div>

        @if ($errors->any())
            <div class="mb-4 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm p-3">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="space-y-4">
            @csrf
            <div>
                <label for="mobile" class="block text-sm font-medium text-slate-700 mb-1">{{ __('site.mobile') }}</label>
                <input id="mobile" name="mobile" type="text" inputmode="numeric" autofocus required
                    value="{{ old('mobile') }}"
                    class="w-full rounded-lg border border-slate-300 px-4 py-3 text-base focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label for="password" class="block text-sm font-medium text-slate-700 mb-1">{{ __('site.password') }}</label>
                <input id="password" name="password" type="password" required
                    class="w-full rounded-lg border border-slate-300 px-4 py-3 text-base focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <label class="flex items-center gap-2 text-sm text-slate-600">
                <input type="checkbox" name="remember" class="rounded border-slate-300">
                {{ __('site.remember_me') }}
            </label>
            <button type="submit"
                class="w-full rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 text-base transition"
                style="min-height:48px">
                {{ __('site.sign_in') }}
            </button>
        </form>
    </div>
</x-layouts.guest>
