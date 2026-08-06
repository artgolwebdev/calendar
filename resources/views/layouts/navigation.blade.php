<nav class="bg-white border-b border-[#E5E5E8]">
    <!-- Primary Navigation Menu -->
    <div class="container">
        <div class="flex justify-between h-14">
            <div class="flex items-center space-x-8 space-x-reverse">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-2 text-base font-semibold text-[#1A1A1E] hover:opacity-90 transition-opacity">
                        <span class="w-6 h-6 rounded bg-[#4F46E5] flex items-center justify-center text-white text-xs font-bold">ל</span>
                        <span>לוח שנה משפחתי</span>
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-6 space-x-reverse sm:-my-px sm:flex h-full">
                    <a href="{{ route('dashboard') }}" 
                       class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium transition-colors {{ request()->routeIs('dashboard') ? 'border-[#4F46E5] text-[#1A1A1E]' : 'border-transparent text-[#6B6B75] hover:text-[#1A1A1E]' }}">
                        לוח בקרה
                    </a>
                    <a href="{{ route('family-members.index') }}" 
                       class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium transition-colors {{ request()->routeIs('family-members.*') ? 'border-[#4F46E5] text-[#1A1A1E]' : 'border-transparent text-[#6B6B75] hover:text-[#1A1A1E]' }}">
                        חברי משפחה
                    </a>
                    <a href="{{ route('media.index') }}" 
                       class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium transition-colors {{ request()->routeIs('media.*') ? 'border-[#4F46E5] text-[#1A1A1E]' : 'border-transparent text-[#6B6B75] hover:text-[#1A1A1E]' }}">
                        הספרייה שלי
                    </a>
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <div class="relative">
                    <button onclick="document.getElementById('userDropdown').classList.toggle('hidden')" class="inline-flex items-center gap-1.5 px-3 py-1.5 border border-[#E5E5E8] rounded-lg text-sm font-medium text-[#1A1A1E] bg-white hover:bg-[#F7F7F8] transition-colors">
                        <div>{{ Auth::user()->name }}</div>
                        <div>
                            <svg class="fill-current h-4 w-4 text-[#6B6B75]" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </div>
                    </button>

                    <div id="userDropdown" class="hidden absolute left-0 mt-2 w-48 bg-white border border-[#E5E5E8] rounded-lg shadow-sm overflow-hidden z-50">
                        <div class="py-1">
                            <a href="{{ route('calendars.create') }}" class="flex items-center gap-2 px-4 py-2 text-sm text-[#1A1A1E] hover:bg-[#F7F7F8] transition-colors">
                                <svg class="w-4 h-4 text-[#4F46E5]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                לוח שנה חדש
                            </a>
                            <div class="border-t border-[#E5E5E8] my-1"></div>
                            <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-[#1A1A1E] hover:bg-[#F7F7F8] transition-colors">
                                פרופיל
                            </a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="block w-full text-right px-4 py-2 text-sm text-[#1A1A1E] hover:bg-[#F7F7F8] transition-colors">
                                    התנתק
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button onclick="document.getElementById('mobileMenu').classList.toggle('hidden')" class="inline-flex items-center justify-center p-2 rounded-lg text-[#6B6B75] hover:bg-[#F7F7F8] hover:text-[#1A1A1E] transition-colors">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div id="mobileMenu" class="hidden sm:hidden border-t border-[#E5E5E8]">
        <div class="pt-2 pb-3 space-y-1">
            <a href="{{ route('dashboard') }}" 
               class="block px-4 py-2 text-sm font-medium transition-colors {{ request()->routeIs('dashboard') ? 'bg-[#EEF2FF] text-[#4F46E5]' : 'text-[#6B6B75] hover:bg-[#F7F7F8] hover:text-[#1A1A1E]' }}">
                לוח בקרה
            </a>
            <a href="{{ route('family-members.index') }}" 
               class="block px-4 py-2 text-sm font-medium transition-colors {{ request()->routeIs('family-members.*') ? 'bg-[#EEF2FF] text-[#4F46E5]' : 'text-[#6B6B75] hover:bg-[#F7F7F8] hover:text-[#1A1A1E]' }}">
                חברי משפחה
            </a>
            <a href="{{ route('media.index') }}" 
               class="block px-4 py-2 text-sm font-medium transition-colors {{ request()->routeIs('media.*') ? 'bg-[#EEF2FF] text-[#4F46E5]' : 'text-[#6B6B75] hover:bg-[#F7F7F8] hover:text-[#1A1A1E]' }}">
                הספרייה שלי
            </a>
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-3 pb-3 border-t border-[#E5E5E8]">
            <div class="px-4 mb-2">
                <div class="font-medium text-sm text-[#1A1A1E]">{{ Auth::user()->name }}</div>
                <div class="font-normal text-xs text-[#6B6B75]">{{ Auth::user()->email }}</div>
            </div>

            <div class="space-y-1">
                <a href="{{ route('calendars.create') }}"
                   class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-[#6B6B75] hover:bg-[#F7F7F8] hover:text-[#1A1A1E] transition-colors">
                    <svg class="w-4 h-4 text-[#4F46E5]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    לוח שנה חדש
                </a>

                <a href="{{ route('profile.edit') }}" 
                   class="block px-4 py-2 text-sm font-medium text-[#6B6B75] hover:bg-[#F7F7F8] hover:text-[#1A1A1E] transition-colors">
                    פרופיל
                </a>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="block w-full text-right px-4 py-2 text-sm font-medium text-[#6B6B75] hover:bg-[#F7F7F8] hover:text-[#1A1A1E] transition-colors">
                        התנתק
                    </button>
                </form>
            </div>
        </div>
    </div>
</nav>

<script>
    // Close dropdown when clicking outside
    document.addEventListener('click', function(event) {
        const dropdown = document.getElementById('userDropdown');
        const button = event.target.closest('button');
        
        if (dropdown && !dropdown.contains(event.target) && !button) {
            dropdown.classList.add('hidden');
        }
    });
</script>
