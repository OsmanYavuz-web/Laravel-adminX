{{--
    Paylaşımlı QuickAddDictionary bileşeni
    Kullanım: <livewire:components.quick-add-dictionary type="region" modalName="quick-add-region" />
--}}
<div>
    <div class="inline-flex">
        <flux:button
            size="sm"
            variant="ghost"
            icon="plus"
            wire:click="openModal"
            x-tooltip="{{ __('Yeni ekle') }}"
        />
    </div>

    <flux:modal wire:model="showModal" class="max-w-md">
        <form wire:submit="save" class="space-y-5">
            <div>
                <flux:heading size="lg">{{ __('Hızlı Ekle') }}</flux:heading>
                <flux:text class="text-zinc-400 mt-1">{{ $typeLabel }}</flux:text>
            </div>

            <x-translatable-input
                name="name"
                :label="__('Ad / Başlık')"
                :value="$name"
                required
            />

            <flux:input
                wire:model="code"
                label="{{ __('Kısa Kod') }}"
                placeholder="Örn: AE, AR"
                description="{{ __('Opsiyonel') }}"
            />

            <div class="flex justify-end gap-3 pt-2">
                <flux:button variant="ghost" wire:click="$set('showModal', false)">{{ __('İptal') }}</flux:button>
                <flux:button type="submit" variant="primary" icon="plus">
                    {{ __('Ekle') }}
                </flux:button>
            </div>
        </form>
    </flux:modal>
</div>
