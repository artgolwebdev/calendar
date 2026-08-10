<x-guest-layout>
    <div class="mb-5">
        <h2 class="text-xl font-semibold text-ink-900">יצירת חשבון חדש</h2>
        <p class="text-xs text-ink-500 mt-1">מלא את הפרטים ליצירת חשבון לוח שנה משפחתי</p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf

        <!-- Name -->
        <div>
            <label for="name" class="label">שם מלא</label>
            <input id="name" class="input" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" placeholder="ישראל ישראלי" />
            <x-input-error :messages="$errors->get('name')" class="mt-1" />
        </div>

        <!-- Email Address -->
        <div>
            <label for="email" class="label">כתובת אימייל</label>
            <input id="email" class="input" type="email" name="email" :value="old('email')" required autocomplete="username" placeholder="name@example.com" />
            <x-input-error :messages="$errors->get('email')" class="mt-1" />
        </div>

        <!-- Password -->
        <div>
            <label for="password" class="label">סיסמה</label>
            <input id="password" class="input"
                            type="password"
                            name="password"
                            required autocomplete="new-password" placeholder="לפחות 8 תווים" />
            <x-input-error :messages="$errors->get('password')" class="mt-1" />
        </div>

        <!-- Confirm Password -->
        <div>
            <label for="password_confirmation" class="label">אימות סיסמה</label>
            <input id="password_confirmation" class="input"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password" placeholder="הזן שוב את הסיסמה" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1" />
        </div>

        <div class="pt-2">
            <button type="submit" class="btn btn-primary w-full justify-center">
                צור חשבון
            </button>
        </div>

        <div class="mt-6 pt-4 border-t border-ink-200 text-center text-xs text-ink-500">
            כבר יש לך חשבון? 
            <a href="{{ route('login') }}" class="font-medium text-ink-500 hover:text-ink-900 transition-colors">
                התחבר למערכת
            </a>
        </div>
    </form>
</x-guest-layout>
