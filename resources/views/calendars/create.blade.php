<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-semibold text-[#1A1A1E]">צור לוח שנה חדש</h1>
            <a href="{{ route('dashboard') }}" class="text-sm font-medium text-[#6B6B75] hover:text-[#1A1A1E] transition-colors">
                → חזור ללוח הבקרה
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="container">
            <div class="card p-6 max-w-xl mx-auto">
                <form action="{{ route('calendars.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    
                    <div>
                        <label for="name" class="label">שם לוח השנה</label>
                        <input type="text" name="name" id="name" required
                            class="input"
                            placeholder="לדוגמה: לוח שנה 2026"
                            value="{{ old('name') }}">
                    </div>

                    <x-cover-upload />

                    @if ($errors->any())
                        <div class="p-4 rounded-lg bg-[#FEF2F2] border border-[#FCA5A5] text-sm text-[#DC2626]">
                            <ul class="list-disc list-inside space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="pt-4 flex items-center gap-3">
                        <button type="submit" class="btn btn-primary">
                            צור לוח שנה
                        </button>
                        <a href="{{ route('dashboard') }}" class="btn btn-secondary">
                            ביטול
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>