<x-guest-layout>
    <div class="mb-5">
        <h2 class="text-xl font-semibold text-ink-900">שכחת את הסיסמה?</h2>
        <p class="text-xs text-ink-500 mt-1">הזן את כתובת האימייל שלך ונשלח לך קישור לאיפוס הסיסמה</p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
        @csrf

        <!-- Email Address -->
        <div>
            <label for="email" class="label">כתובת אימייל</label>
            <input id="email" class="input" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" placeholder="name@example.com" />
            <x-input-error :messages="$errors->get('email')" class="mt-1" />
        </div>

        <div class="pt-2">
            <button type="submit" class="btn btn-primary w-full justify-center">
                שלח קישור איפוס
            </button>
        </div>

        <div class="mt-6 pt-4 border-t border-ink-200 text-center text-xs text-ink-500">
            זכרת את הסיסמה?
            <a href="{{ route('login') }}" class="font-medium text-ink-500 hover:text-ink-900 transition-colors">
                התחבר עכשיו
            </a>
        </div>
    </form>
</x-guest-layout>
