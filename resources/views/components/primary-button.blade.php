<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center gap-2 h-10 px-4 py-2 bg-[#4F46E5] border border-transparent rounded-lg font-medium text-sm text-white hover:bg-[#4338CA] focus:outline-none focus:ring-2 focus:ring-[#4F46E5] focus:ring-offset-2 transition-colors duration-150']) }}>
    {{ $slot }}
</button>
