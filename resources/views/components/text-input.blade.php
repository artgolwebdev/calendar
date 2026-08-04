@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'h-10 px-3 border-[#E5E5E8] focus:border-[#4F46E5] focus:ring-1 focus:ring-[#4F46E5] rounded-lg text-sm text-[#1A1A1E] placeholder:text-[#9CA3AF] bg-white transition-colors']) }}>
