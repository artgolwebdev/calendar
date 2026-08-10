@props([
    'name' => '',
    'label' => '',
    'values' => [],
    'placeholder' => 'הקישו ועשו Enter',
])

<div x-data="tagInput({ name: @js($name), values: @js($values) })" class="space-y-2">
    @if ($label)
        <label class="label">{{ $label }}</label>
    @endif

    <div class="flex flex-wrap items-center gap-1.5 p-2 rounded-lg border border-ink-200 bg-white focus-within:border-ink-900 focus-within:ring-2 focus-within:ring-volt/50 transition-colors">
        <template x-for="(tag, index) in tags" :key="tag">
            <span class="inline-flex items-center gap-1.5 h-7 px-2.5 rounded-full bg-ink-100 text-ink-900 text-xs font-semibold">
                <span x-text="tag"></span>
                <button type="button" @click="removeTag(index)" :title="'הסרת ' + tag"
                    class="flex items-center justify-center w-4 h-4 rounded-full text-ink-400 hover:text-danger transition-colors">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M6 6l12 12M18 6L6 18" />
                    </svg>
                </button>
            </span>
        </template>

        <input type="text" x-model="draft"
            @keydown.enter.prevent="addDraft()"
            @keydown.comma.prevent="addDraft()"
            @keydown.backspace="onBackspace()"
            @blur="addDraft()"
            class="flex-1 min-w-[6rem] h-7 outline-none text-sm text-ink-900 bg-transparent placeholder:text-ink-400"
            :placeholder="tags.length ? '' : @js($placeholder)">
    </div>

    <p class="text-xs text-ink-400">הקישו ערך ולחצו Enter או פסיק כדי להוסיף, ולחיצה על x תסיר אותו</p>

    <template x-for="(tag, index) in tags" :key="index">
        <input type="hidden" :name="name + '[]'" :value="tag">
    </template>
</div>
