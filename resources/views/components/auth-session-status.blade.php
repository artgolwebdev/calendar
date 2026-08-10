@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'font-medium text-sm text-[#16A34A]']) }}>
        {{ $status }}
    </div>
@endif
