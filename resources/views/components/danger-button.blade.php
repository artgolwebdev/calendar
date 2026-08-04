<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center gap-2 h-10 px-4 py-2 bg-[#DC2626] border border-transparent rounded-lg font-medium text-sm text-white hover:bg-[#B91C1C] focus:outline-none focus:ring-2 focus:ring-[#DC2626] focus:ring-offset-2 transition-colors duration-150']) }}>
    {{ $slot }}
</button>
