@props(['value'])

<label {{ $attributes->merge(['class' => 'block font-medium text-sm text-[#6B6B75] mb-1.5']) }}>
    {{ $value ?? $slot }}
</label>
