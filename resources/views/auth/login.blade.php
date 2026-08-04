<x-guest-layout>
    <div class="mb-5">
        <h2 class="text-xl font-semibold text-[#1A1A1E]">התחברות למערכת</h2>
        <p class="text-xs text-[#6B6B75] mt-1">הזן את פרטי החשבון שלך כדי להיכנס</p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        <!-- Email Address -->
        <div>
            <label for="email" class="label">כתובת אימייל</label>
            <input id="email" class="input" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" placeholder="name@example.com" />
            <x-input-error :messages="$errors->get('email')" class="mt-1" />
        </div>

        <!-- Password -->
        <div>
            <div class="flex items-center justify-between mb-1">
                <label for="password" class="label !mb-0">סיסמה</label>
                @if (Route::has('password.request'))
                    <a class="text-xs font-medium text-[#4F46E5] hover:text-[#4338CA] transition-colors" href="{{ route('password.request') }}">
                        שכחת סיסמה?
                    </a>
                @endif
            </div>

            <input id="password" class="input"
                            type="password"
                            name="password"
                            required autocomplete="current-password" placeholder="••••••••" />

            <x-input-error :messages="$errors->get('password')" class="mt-1" />
        </div>

        <!-- Remember Me -->
        <div class="flex items-center">
            <label for="remember_me" class="inline-flex items-center gap-2 cursor-pointer">
                <input id="remember_me" type="checkbox" class="rounded border-[#E5E5E8] text-[#4F46E5] focus:ring-[#4F46E5]" name="remember">
                <span class="text-xs font-medium text-[#6B6B75]">זכור אותי במכשיר זה</span>
            </label>
        </div>

        <div class="pt-2">
            <button type="submit" class="btn btn-primary w-full justify-center">
                התחבר
            </button>
        </div>

        <div class="mt-6 pt-4 border-t border-[#E5E5E8] text-center text-xs text-[#6B6B75]">
            עדיין אין לך חשבון? 
            <a href="{{ route('register') }}" class="font-medium text-[#4F46E5] hover:text-[#4338CA] transition-colors">
                הרשם עכשיו
            </a>
        </div>
    </form>
</x-guest-layout>
