<x-guest-layout>
    <div class="mb-5">
        <h2 class="text-xl font-semibold text-[#1A1A1E]">איפוס סיסמה</h2>
        <p class="text-xs text-[#6B6B75] mt-1">בחר סיסמה חדשה לחשבון שלך</p>
    </div>

    <form method="POST" action="{{ route('password.store') }}" class="space-y-4">
        @csrf

        <!-- Password Reset Token -->
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <!-- Email Address -->
        <div>
            <label for="email" class="label">כתובת אימייל</label>
            <input id="email" class="input" type="email" name="email" :value="old('email', $request->email)" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-1" />
        </div>

        <!-- Password -->
        <div>
            <label for="password" class="label">סיסמה חדשה</label>
            <input id="password" class="input" type="password" name="password" required autocomplete="new-password" placeholder="••••••••" />
            <x-input-error :messages="$errors->get('password')" class="mt-1" />
        </div>

        <!-- Confirm Password -->
        <div>
            <label for="password_confirmation" class="label">אישור סיסמה חדשה</label>
            <input id="password_confirmation" class="input" type="password" name="password_confirmation" required autocomplete="new-password" placeholder="••••••••" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1" />
        </div>

        <div class="pt-2">
            <button type="submit" class="btn btn-primary w-full justify-center">
                איפוס סיסמה
            </button>
        </div>
    </form>
</x-guest-layout>
