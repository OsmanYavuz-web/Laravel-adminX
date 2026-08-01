@props([
    'name' => '',
    'label' => '',
    'languages' => [],
    'value' => [],
    'required' => false,
    'placeholder' => '',
    'type' => 'input',
])

@php
    $langs = $languages ?: \App\Models\Language::getActive();
    $defaultCode = collect($langs)->firstWhere('is_default', true)['code'] ?? ($langs[0]['code'] ?? 'tr');
    $componentId = 'translatable-' . str_replace('.', '-', $name) . '-' . uniqid();
@endphp

<div
    x-data="{
        activeTab: '{{ $defaultCode }}',
        values: @js(is_array($value) ? $value : (is_string($value) ? (json_decode($value, true) ?: []) : [])),
        init() {
            // Initialize empty values for each language
            @foreach($langs as $lang)
                if (!this.values['{{ $lang['code'] }}']) {
                    this.values['{{ $lang['code'] }}'] = '';
                }
            @endforeach
        }
    }"
    id="{{ $componentId }}"
    class="space-y-2"
>
    @if($label)
        <label class="block text-sm font-semibold text-zinc-800 dark:text-zinc-200">
            {{ $label }}
            @if($required)
                <span class="ml-1 text-red-500 font-bold cursor-help" title="{{ __('Zorunlu alan') }}">*</span>
            @endif
        </label>
    @endif

    {{-- Language Tabs --}}
    <div class="flex items-center gap-1 border-b border-zinc-200 dark:border-zinc-700">
        @foreach($langs as $lang)
            <button
                type="button"
                @click="activeTab = '{{ $lang['code'] }}'"
                :class="activeTab === '{{ $lang['code'] }}'
                    ? 'border-b-2 border-brand text-brand dark:text-brand-accent font-bold'
                    : 'text-zinc-500 hover:text-zinc-700 dark:hover:text-zinc-300'"
                class="flex items-center gap-1.5 px-3 py-2 text-xs transition-all cursor-pointer"
            >
                <span>{{ $lang['flag'] }}</span>
                <span>{{ strtoupper($lang['code']) }}</span>
            </button>
        @endforeach
    </div>

    {{-- Inputs per language --}}
    @foreach($langs as $lang)
        <div x-show="activeTab === '{{ $lang['code'] }}'" x-cloak>
            @if($type === 'textarea')
                <textarea
                    x-model="values['{{ $lang['code'] }}']"
                    wire:model.defer="{{ $name }}.{{ $lang['code'] }}"
                    placeholder="{{ $placeholder ?: $lang['native_name'] }}"
                    @if($required && $lang['code'] === $defaultCode) required @endif
                    class="w-full rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-4 py-2.5 text-sm text-zinc-900 dark:text-white placeholder-zinc-400 focus:border-brand focus:ring-1 focus:ring-brand/30 transition-colors"
                    rows="3"
                ></textarea>
            @else
                <input
                    type="text"
                    x-model="values['{{ $lang['code'] }}']"
                    wire:model.defer="{{ $name }}.{{ $lang['code'] }}"
                    placeholder="{{ $placeholder ?: $lang['native_name'] }}"
                    @if($required && $lang['code'] === $defaultCode) required @endif
                    class="w-full rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-4 py-2.5 text-sm text-zinc-900 dark:text-white placeholder-zinc-400 focus:border-brand focus:ring-1 focus:ring-brand/30 transition-colors"
                />
            @endif
        </div>
    @endforeach
</div>
