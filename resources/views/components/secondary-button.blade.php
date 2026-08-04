<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center justify-center gap-2 h-10 px-4 py-2 bg-white border border-[#E5E5E8] rounded-lg font-medium text-sm text-[#1A1A1E] hover:bg-[#F7F7F8] hover:border-[#D4D4D8] focus:outline-none focus:ring-2 focus:ring-[#4F46E5] focus:ring-offset-2 disabled:opacity-50 transition-colors duration-150']) }}>
    {{ $slot }}
</button>
