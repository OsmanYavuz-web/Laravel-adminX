<div class="space-y-6">
    {{-- Başlık --}}
    <div class="flex items-center justify-between">
        <div>
            <flux:heading size="xl">{{ __('Sözlükler') }}</flux:heading>
            <flux:text class="mt-1 text-zinc-500">{{ __('Dönem, Metal, Birim, Bölge, Darphane ve diğer referans verileri') }}</flux:text>
        </div>
        @can('dictionaries.create')
            <flux:button icon="plus" variant="primary" wire:click="create">
                {{ __('Yeni Ekle') }}
            </flux:button>
        @endcan
    </div>

    {{-- Tip Sekmeleri --}}
    <div class="flex flex-wrap gap-2">
        @foreach($this->types as $typeKey => $typeNames)
            @php $label = $typeNames[app()->getLocale()] ?? $typeNames['tr']; @endphp
            <button
                wire:click="setType('{{ $typeKey }}')"
                type="button"
                @class([
                    'px-4 py-2 rounded-lg text-sm font-medium transition-colors',
                    'bg-brand text-white shadow-sm' => $activeType === $typeKey,
                    'bg-white dark:bg-zinc-800 text-zinc-600 dark:text-zinc-300 border border-zinc-200 dark:border-zinc-700 hover:bg-zinc-50 dark:hover:bg-zinc-700' => $activeType !== $typeKey,
                ])
            >
                {{ $label }}
                <span @class([
                    'ml-1.5 text-xs px-1.5 py-0.5 rounded-full',
                    'bg-white/25 text-white' => $activeType === $typeKey,
                    'bg-zinc-100 dark:bg-zinc-700 text-zinc-500' => $activeType !== $typeKey,
                ])>
                    {{ \App\Models\Dictionary::where('type', $typeKey)->count() }}
                </span>
            </button>
        @endforeach
    </div>

    {{-- Tablo --}}
    <flux:card>
        @if($this->items->isEmpty())
            <div class="py-12 text-center">
                <flux:icon icon="circle-stack" class="mx-auto size-10 text-zinc-300 dark:text-zinc-600" />
                <flux:text class="mt-3 text-zinc-400">{{ __('Henüz kayıt yok. Yeni ekle butonuna tıklayın.') }}</flux:text>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-zinc-600 dark:text-zinc-400">
                    <thead class="bg-zinc-50 dark:bg-zinc-800/60 text-xs font-bold uppercase text-zinc-500 border-b border-zinc-200 dark:border-zinc-800">
                        <tr>
                            @foreach($this->activeLanguages as $lang)
                                <th class="px-6 py-4">
                                    <span class="inline-flex items-center gap-1.5">
                                        <span>{{ $lang['flag'] }}</span>
                                        <span>{{ $lang['native_name'] }}</span>
                                    </span>
                                </th>
                            @endforeach
                            <th class="px-6 py-4">{{ __('Kod') }}</th>
                            <th class="px-6 py-4">{{ __('Sıra') }}</th>
                            <th class="px-6 py-4">{{ __('Durum') }}</th>
                            <th class="px-6 py-4 text-right"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                        @foreach($this->items as $item)
                            <tr wire:key="dict-{{ $item->id }}" class="hover:bg-zinc-50/60 dark:hover:bg-zinc-800/40 transition-colors">
                                @foreach($this->activeLanguages as $lang)
                                    <td class="px-6 py-4 text-sm {{ $loop->first ? 'font-medium text-zinc-900 dark:text-white' : 'text-zinc-500 dark:text-zinc-400' }}">
                                        {{ $item->getTranslation('name', $lang['code'], false) ?: '—' }}
                                    </td>
                                @endforeach
                                <td class="px-6 py-4">
                                    @if($item->code)
                                        <flux:badge color="blue" size="sm">{{ $item->code }}</flux:badge>
                                    @else
                                        <span class="text-zinc-300">—</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-zinc-400 text-sm">{{ $item->sort_order }}</td>
                                <td class="px-6 py-4">
                                    @can('dictionaries.update')
                                        <button
                                            wire:click="toggleActive({{ $item->id }})"
                                            type="button"
                                            class="cursor-pointer"
                                        >
                                            @if($item->is_active)
                                                <flux:badge color="green" size="sm" icon="check-circle">{{ __('Aktif') }}</flux:badge>
                                            @else
                                                <flux:badge color="zinc" size="sm" icon="x-circle">{{ __('Pasif') }}</flux:badge>
                                            @endif
                                        </button>
                                    @else
                                        @if($item->is_active)
                                            <flux:badge color="green" size="sm">{{ __('Aktif') }}</flux:badge>
                                        @else
                                            <flux:badge color="zinc" size="sm">{{ __('Pasif') }}</flux:badge>
                                        @endif
                                    @endcan
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex justify-end gap-1">
                                        @can('dictionaries.update')
                                            <flux:button size="sm" variant="ghost" icon="pencil" wire:click="edit({{ $item->id }})" />
                                        @endcan
                                        @can('dictionaries.delete')
                                            <flux:button size="sm" variant="ghost" icon="trash" class="text-red-500 hover:text-red-600" wire:click="confirmDelete({{ $item->id }})" />
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </flux:card>

{{-- Form Modal --}}
<flux:modal wire:model="showFormModal" class="max-w-lg">
    <form wire:submit="save" class="space-y-5">
        <div>
            <flux:heading size="lg">
                {{ $editingId ? __('Kaydı Düzenle') : __('Yeni Kayıt Ekle') }}
            </flux:heading>
            <flux:text class="text-zinc-400 mt-1">
                {{ ($this->types[$activeType][app()->getLocale()] ?? $activeType) }}
            </flux:text>
        </div>

        <x-translatable-input
            name="formName"
            :label="__('Ad / Başlık')"
            :value="$formName"
            required
        />

        <div class="grid grid-cols-2 gap-4">
            <flux:input
                wire:model="formCode"
                label="{{ __('Kısa Kod') }}"
                placeholder="Örn: AE, AR"
                description="{{ __('Opsiyonel') }}"
            />
            <flux:input
                wire:model.number="formSort"
                type="number"
                label="{{ __('Sıralama') }}"
                min="0"
            />
        </div>

        <flux:checkbox wire:model="formActive" label="{{ __('Aktif') }}" />

        <div class="flex justify-end gap-3 pt-2">
            <flux:button variant="ghost" wire:click="$set('showFormModal', false)">{{ __('İptal') }}</flux:button>
            <flux:button type="submit" variant="primary">
                {{ $editingId ? __('Güncelle') : __('Kaydet') }}
            </flux:button>
        </div>
    </form>
</flux:modal>

{{-- Silme Onay Modal --}}
<flux:modal wire:model="showDeleteModal" class="max-w-sm">
    <div class="space-y-4">
        <flux:heading size="lg">{{ __('Kaydı Sil') }}</flux:heading>
        <flux:text class="text-zinc-500">
            {{ __('Bu kaydı silmek istediğinizden emin misiniz? Bu işlem geri alınamaz.') }}
        </flux:text>
        <div class="flex justify-end gap-3">
            <flux:button variant="ghost" wire:click="$set('showDeleteModal', false)">{{ __('Vazgeç') }}</flux:button>
            <flux:button variant="danger" wire:click="delete">{{ __('Sil') }}</flux:button>
        </div>
    </div>
</flux:modal>
</div>
