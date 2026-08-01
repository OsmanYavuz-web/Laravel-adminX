<div class="max-w-5xl mx-auto space-y-6" x-data="{ activeTab: $wire.entangle('activeTab') }">

    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 text-sm text-zinc-400">
        <a href="{{ route('excavation-projects.index') }}" wire:navigate class="hover:text-zinc-600 dark:hover:text-zinc-200">{{ __('Kazı Projeleri') }}</a>
        @if($project && $project->exists)
            <flux:icon icon="chevron-right" class="size-4" />
            <a href="{{ route('finds.index', $project) }}" wire:navigate class="hover:text-zinc-600 dark:hover:text-zinc-200">{{ $project->name }}</a>
        @endif
        @if($find && $find->exists)
            <flux:icon icon="chevron-right" class="size-4" />
            <a href="{{ route('coins.index', [$find->project, $find]) }}" wire:navigate class="hover:text-zinc-600 dark:hover:text-zinc-200">{{ $find->inventory_number }}</a>
        @endif
        <flux:icon icon="chevron-right" class="size-4" />
        <span class="text-zinc-600 dark:text-zinc-300">{{ isset($coin) ? __('Düzenle') : __('Yeni Sikke') }}</span>
    </div>

    <flux:heading size="xl">{{ isset($coin) ? __('Sikke Düzenle') : __('Yeni Sikke Ekle') }}</flux:heading>

    {{-- Sekmeler --}}
    @php
        $activeProject = ($find && $find->exists) ? $find->project : ($project && $project->exists ? $project : null);
        if (!$activeProject && isset($findId) && $findId) {
            $fObj = \App\Modules\ExcaCoin\Models\Find::with('project')->find($findId);
            $activeProject = $fObj?->project;
        }
        $tabFieldMap = [
            'identification' => ['period_id', 'authority_id', 'ruler_id', 'region_id', 'mint_id', 'denomination_id', 'date_range'],
            'physical'       => ['metal_id', 'diameter', 'weight', 'axis', 'is_cut', 'is_pierced'],
            'obverse'        => ['obverse_legend', 'obverse_legend_expanded', 'obverse_description'],
            'reverse'        => ['reverse_legend', 'reverse_legend_expanded', 'reverse_description'],
            'extra'          => ['mint_mark', 'magistrate', 'control_mark', 'monogram', 'countermark', 'reference', 'note', 'is_overstrike'],
            'media'          => ['coin_photos'],
        ];
    @endphp
    <div class="flex flex-wrap gap-1 bg-zinc-100 dark:bg-zinc-800/60 rounded-xl p-1.5">
        @foreach([
            ['id' => 'identification', 'label' => __('Tanımlama'),    'icon' => 'tag'],
            ['id' => 'physical',      'label' => __('Fiziksel'),      'icon' => 'scale'],
            ['id' => 'obverse',       'label' => __('Ön Yüz'),       'icon' => 'sun'],
            ['id' => 'reverse',       'label' => __('Arka Yüz'),     'icon' => 'moon'],
            ['id' => 'extra',         'label' => __('Ekstra'),        'icon' => 'ellipsis-horizontal-circle'],
            ['id' => 'media',         'label' => __('Görseller'),     'icon' => 'photo'],
        ] as $tab)
            @if(empty($tabFieldMap[$tab['id']]) || !$activeProject || $activeProject->hasAnyFieldVisible($tabFieldMap[$tab['id']]))
                <button
                    type="button"
                    @click="activeTab = '{{ $tab['id'] }}'"
                    :class="activeTab === '{{ $tab['id'] }}'
                        ? 'bg-white dark:bg-zinc-700 shadow-sm text-zinc-900 dark:text-zinc-100'
                        : 'text-zinc-500 hover:text-zinc-700 dark:hover:text-zinc-300'"
                    class="flex items-center gap-1.5 px-3.5 py-2 rounded-lg text-sm font-medium transition-all"
                >
                    <flux:icon icon="{{ $tab['icon'] }}" class="size-4" />
                    {{ $tab['label'] }}
                </button>
            @endif
        @endforeach
    </div>

    <form wire:submit="save" class="space-y-5" enctype="multipart/form-data">

        @php
            $activeProject = ($find && $find->exists) ? $find->project : ($project && $project->exists ? $project : null);
            if (!$activeProject && isset($findId) && $findId) {
                $fObj = \App\Modules\ExcaCoin\Models\Find::with('project')->find($findId);
                $activeProject = $fObj?->project;
            }
        @endphp

        {{-- ═══════════════════════════════════════════════════ --}}
        {{-- SEKME 1: TANIMLAMA                                  --}}
        {{-- ═══════════════════════════════════════════════════ --}}
        <div x-show="activeTab === 'identification'" x-transition>
            <flux:card class="space-y-5">
                @if(!$find || !$find->exists)
                    <flux:field>
                        <flux:label>
                            {{ __('Bağlı Buluntu') }} <span class="ml-1 text-red-500 font-bold" title="{{ __('Zorunlu alan') }}">*</span>
                            <svg class="inline size-3.5 text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200 cursor-help ml-1" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><title>{{ __('Sikkenin kaydedildiği ana buluntu envanter kaydı') }}</title><path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" /></svg>
                        </flux:label>
                        <flux:select wire:model.live="findId" x-searchable required>
                            <flux:select.option value="">— {{ __('Buluntu Seçin...') }} —</flux:select.option>
                            @foreach(\App\Modules\ExcaCoin\Models\Find::query()->whereHas('project', fn($q) => $q->accessibleBy())->with('project')->get() as $f)
                                <flux:select.option value="{{ $f->id }}">
                                    {{ $f->inventory_number }} ({{ $f->project->name }} — {{ $f->excavation_area }})
                                </flux:select.option>
                            @endforeach
                        </flux:select>
                    </flux:field>
                @endif

                {{-- Dönem --}}
                @if(!$activeProject || $activeProject->isFieldVisible('period_id'))
                    <div class="flex items-end gap-2">
                        <div class="flex-1">
                            <flux:select wire:model="periodId" label="{{ __('Dönem') }}" x-searchable :placeholder="__('Seçin...')">
                                <flux:select.option value="">— {{ __('Seçilmedi') }} —</flux:select.option>
                                @foreach($this->periods as $item)
                                    <flux:select.option value="{{ $item->id }}">
                                        {{ $item->getTranslation('name', app()->getLocale(), false) }}
                                    </flux:select.option>
                                @endforeach
                            </flux:select>
                        </div>
                        @can('dictionaries.create')
                            <livewire:exca-coin.components.quick-add-dictionary type="period" modalName="quick-add-period" wire:key="qad-period" />
                        @endcan
                    </div>
                @endif

                {{-- Otorite --}}
                @if(!$activeProject || $activeProject->isFieldVisible('authority_id'))
                    <div class="flex items-end gap-2">
                        <div class="flex-1">
                            <flux:select wire:model="authorityId" label="{{ __('Otorite') }}" x-searchable :placeholder="__('Seçin...')">
                                <flux:select.option value="">— {{ __('Seçilmedi') }} —</flux:select.option>
                                @foreach($this->authorities as $item)
                                    <flux:select.option value="{{ $item->id }}">{{ $item->getTranslation('name', app()->getLocale(), false) }}</flux:select.option>
                                @endforeach
                            </flux:select>
                        </div>
                        @can('dictionaries.create')
                            <livewire:exca-coin.components.quick-add-dictionary type="authority" modalName="quick-add-authority" wire:key="qad-authority" />
                        @endcan
                    </div>
                @endif

                {{-- Hükümdar --}}
                @if(!$activeProject || $activeProject->isFieldVisible('ruler_id'))
                    <div class="flex items-end gap-2">
                        <div class="flex-1">
                            <flux:select wire:model="rulerId" label="{{ __('Hükümdar') }}" x-searchable :placeholder="__('Seçin...')">
                                <flux:select.option value="">— {{ __('Seçilmedi') }} —</flux:select.option>
                                @foreach($this->rulers as $item)
                                    <flux:select.option value="{{ $item->id }}">{{ $item->getTranslation('name', app()->getLocale(), false) }}</flux:select.option>
                                @endforeach
                            </flux:select>
                        </div>
                        @can('dictionaries.create')
                            <livewire:exca-coin.components.quick-add-dictionary type="ruler" modalName="quick-add-ruler" wire:key="qad-ruler" />
                        @endcan
                    </div>
                @endif

                {{-- Bölge --}}
                @if(!$activeProject || $activeProject->isFieldVisible('region_id'))
                    <div class="flex items-end gap-2">
                        <div class="flex-1">
                            <flux:select wire:model="regionId" label="{{ __('Bölge') }}" x-searchable :placeholder="__('Seçin...')">
                                <flux:select.option value="">— {{ __('Seçilmedi') }} —</flux:select.option>
                                @foreach($this->regions as $item)
                                    <flux:select.option value="{{ $item->id }}">{{ $item->getTranslation('name', app()->getLocale(), false) }}</flux:select.option>
                                @endforeach
                            </flux:select>
                        </div>
                        @can('dictionaries.create')
                            <livewire:exca-coin.components.quick-add-dictionary type="region" modalName="quick-add-region" wire:key="qad-region" />
                        @endcan
                    </div>
                @endif

                {{-- Darphane --}}
                @if(!$activeProject || $activeProject->isFieldVisible('mint_id'))
                    <div class="flex items-end gap-2">
                        <div class="flex-1">
                            <flux:select wire:model="mintId" label="{{ __('Darphane') }}" x-searchable :placeholder="__('Seçin...')">
                                <flux:select.option value="">— {{ __('Seçilmedi') }} —</flux:select.option>
                                @foreach($this->mints as $item)
                                    <flux:select.option value="{{ $item->id }}">{{ $item->getTranslation('name', app()->getLocale(), false) }}</flux:select.option>
                                @endforeach
                            </flux:select>
                        </div>
                        @can('dictionaries.create')
                            <livewire:exca-coin.components.quick-add-dictionary type="mint" modalName="quick-add-mint" wire:key="qad-mint" />
                        @endcan
                    </div>
                @endif

                {{-- Tarih Aralığı --}}
                @if(!$activeProject || $activeProject->isFieldVisible('date_range'))
                    <flux:input
                        wire:model="dateRange"
                        label="{{ __('Tarih Aralığı') }}"
                        placeholder="{{ __('MÖ 2 - MÖ 1 / MS 194-217') }}"
                        description="{{ __('Serbest metin formatında') }}"
                    />
                @endif
            </flux:card>
        </div>

        {{-- ═══════════════════════════════════════════════════ --}}
        {{-- SEKME 2: FİZİKSEL ÖZELLİKLER                       --}}
        {{-- ═══════════════════════════════════════════════════ --}}
        <div x-show="activeTab === 'physical'" x-transition>
            <flux:card class="space-y-5">
                {{-- Metal --}}
                @if(!$activeProject || $activeProject->isFieldVisible('metal_id'))
                    <div class="flex items-end gap-2">
                        <div class="flex-1">
                            <flux:select wire:model="metalId" label="{{ __('Metal') }}" x-searchable :placeholder="__('Seçin...')">
                                <flux:select.option value="">— {{ __('Seçilmedi') }} —</flux:select.option>
                                @foreach($this->metals as $item)
                                    <flux:select.option value="{{ $item->id }}">
                                        @if($item->code) {{ $item->code }} — @endif
                                        {{ $item->getTranslation('name', app()->getLocale(), false) }}
                                    </flux:select.option>
                                @endforeach
                            </flux:select>
                        </div>
                        @can('dictionaries.create')
                            <livewire:exca-coin.components.quick-add-dictionary type="metal" modalName="quick-add-metal" wire:key="qad-metal" />
                        @endcan
                    </div>
                @endif

                <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                    @if(!$activeProject || $activeProject->isFieldVisible('diameter')) <flux:input wire:model="diameter" type="number" step="0.01" label="{{ __('Çap (mm)') }}" placeholder="20.50" /> @endif
                    @if(!$activeProject || $activeProject->isFieldVisible('weight')) <flux:input wire:model="weight" type="number" step="0.001" label="{{ __('Ağırlık (gr)') }}" placeholder="4.250" /> @endif
                    @if(!$activeProject || $activeProject->isFieldVisible('axis'))
                        <div>
                            <flux:label>{{ __('Kalıp Yönü (Axis)') }}</flux:label>
                            <div class="mt-1 grid grid-cols-6 gap-1">
                                @foreach(range(1, 12) as $h)
                                    <label class="cursor-pointer">
                                        <input type="radio" wire:model.number="axis" value="{{ $h }}" class="sr-only peer" />
                                        <div class="text-center py-1.5 rounded-lg border text-xs font-medium
                                            peer-checked:bg-amber-500 peer-checked:text-white peer-checked:border-amber-500
                                            border-zinc-200 dark:border-zinc-700 text-zinc-500 hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-colors">
                                            {{ $h }}
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                            <flux:description>{{ __('1-12 saat yönü') }}</flux:description>
                        </div>
                    @endif
                </div>

                {{-- Birim --}}
                @if(!$activeProject || $activeProject->isFieldVisible('denomination_id'))
                    <div class="flex items-end gap-2">
                        <div class="flex-1">
                            <flux:select wire:model="denominationId" label="{{ __('Birim (Denomination)') }}" x-searchable :placeholder="__('Seçin...')">
                                <flux:select.option value="">— {{ __('Seçilmedi') }} —</flux:select.option>
                                @foreach($this->denominations as $item)
                                    <flux:select.option value="{{ $item->id }}">{{ $item->getTranslation('name', app()->getLocale(), false) }}</flux:select.option>
                                @endforeach
                            </flux:select>
                        </div>
                        @can('dictionaries.create')
                            <livewire:exca-coin.components.quick-add-dictionary type="denomination" modalName="quick-add-denomination" wire:key="qad-denomination" />
                        @endcan
                    </div>
                @endif

                <div class="flex gap-6">
                    @if(!$activeProject || $activeProject->isFieldVisible('is_cut')) <flux:checkbox wire:model="isCut" label="{{ __('Kesilmiş / Kırpılmış') }}" /> @endif
                    @if(!$activeProject || $activeProject->isFieldVisible('is_pierced')) <flux:checkbox wire:model="isPierced" label="{{ __('Delinmiş') }}" /> @endif
                </div>
            </flux:card>
        </div>

        {{-- ═══════════════════════════════════════════════════ --}}
        {{-- SEKME 3: ÖN YÜZ                                     --}}
        {{-- ═══════════════════════════════════════════════════ --}}
        <div x-show="activeTab === 'obverse'" x-transition>
            <flux:card class="space-y-5">
                @if(!$activeProject || $activeProject->isFieldVisible('obverse_description'))
                    <flux:textarea wire:model="obverseDescription" label="{{ __('Ön Yüz Tasviri') }}" rows="4"
                        placeholder="{{ __('Ön yüzdeki portre, figür, tanrı/tanrıça, sembol vb. tasvir') }}" />
                @endif
                @if(!$activeProject || $activeProject->isFieldVisible('obverse_legend'))
                    <flux:input wire:model="obverseLegend" label="{{ __('Ön Yüz Lejandı') }}"
                        placeholder="{{ __('Orijinal yazım / transkripsiyon') }}"
                        description="{{ __('Kısaltılmış lejandın yazılışı') }}" />
                @endif
                @if(!$activeProject || $activeProject->isFieldVisible('obverse_legend_expanded'))
                    <flux:input wire:model="obverseLegendExpanded" label="{{ __('Ön Yüz Lejandı Açılımı') }}"
                        placeholder="{{ __('Tam açılım') }}"
                        description="{{ __('Kısaltılmış lejandın açık hali') }}" />
                @endif
            </flux:card>
        </div>

        {{-- ═══════════════════════════════════════════════════ --}}
        {{-- SEKME 4: ARKA YÜZ                                   --}}
        {{-- ═══════════════════════════════════════════════════ --}}
        <div x-show="activeTab === 'reverse'" x-transition>
            <flux:card class="space-y-5">
                @if(!$activeProject || $activeProject->isFieldVisible('reverse_description'))
                    <flux:textarea wire:model="reverseDescription" label="{{ __('Arka Yüz Tasviri') }}" rows="4"
                        placeholder="{{ __('Arka yüzdeki figür, tanrı/tanrıça, sembol, yapı vb. tasvir') }}" />
                @endif
                @if(!$activeProject || $activeProject->isFieldVisible('reverse_legend'))
                    <flux:input wire:model="reverseLegend" label="{{ __('Arka Yüz Lejandı') }}"
                        placeholder="{{ __('Orijinal yazım / transkripsiyon') }}" />
                @endif
                @if(!$activeProject || $activeProject->isFieldVisible('reverse_legend_expanded'))
                    <flux:input wire:model="reverseLegendExpanded" label="{{ __('Arka Yüz Lejandı Açılımı') }}"
                        placeholder="{{ __('Tam açılım') }}" />
                @endif
            </flux:card>
        </div>

        {{-- ═══════════════════════════════════════════════════ --}}
        {{-- SEKME 5: EKSTRA TANIMLAYICILAR                      --}}
        {{-- ═══════════════════════════════════════════════════ --}}
        <div x-show="activeTab === 'extra'" x-transition>
            <flux:card class="space-y-5">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    @if(!$activeProject || $activeProject->isFieldVisible('mint_mark')) <flux:input wire:model="mintMark" label="{{ __('Darphane İşareti') }}" /> @endif
                    @if(!$activeProject || $activeProject->isFieldVisible('magistrate')) <flux:input wire:model="magistrate" label="{{ __('Magistrat / Yetkili') }}" /> @endif
                    @if(!$activeProject || $activeProject->isFieldVisible('control_mark')) <flux:input wire:model="controlMark" label="{{ __('Kontrol İşareti') }}" /> @endif
                    @if(!$activeProject || $activeProject->isFieldVisible('monogram')) <flux:input wire:model="monogram" label="{{ __('Monogram') }}" /> @endif
                    @if(!$activeProject || $activeProject->isFieldVisible('countermark')) <flux:input wire:model="countermark" label="{{ __('Kontrmark') }}" /> @endif
                </div>

                @if(!$activeProject || $activeProject->isFieldVisible('is_overstrike'))
                    <flux:checkbox wire:model="isOverstrike" label="{{ __('Üst Baskı (Overstrike)') }}"
                        description="{{ __('Başka bir sikkenin üzerine yeniden basılmış') }}" />
                @endif

                @if(!$activeProject || $activeProject->isFieldVisible('reference'))
                    <flux:textarea wire:model="reference" label="{{ __('Referans') }}" rows="3"
                        placeholder="{{ __('RPC IV 3456 / SNG BN 1234 / BMC 56') }}"
                        description="{{ __('Numismatik katalog veya veri tabanı referansı') }}" />
                @endif
                @if(!$activeProject || $activeProject->isFieldVisible('note'))
                    <flux:textarea wire:model="note" label="{{ __('Açıklama / Kondisyon') }}" rows="3"
                        placeholder="{{ __('Kondisyon, ek gözlemler...') }}" />
                @endif
            </flux:card>
        </div>

        {{-- ═══════════════════════════════════════════════════ --}}
        {{-- SEKME 6: GÖRSELLER & BELGELER                       --}}
        {{-- ═══════════════════════════════════════════════════ --}}
        <div x-show="activeTab === 'media'" x-transition>
            <flux:card class="space-y-6">

                {{-- Mevcut medyayı göster (edit modunda) --}}
                @isset($coin)
                    <div class="grid grid-cols-2 gap-6">
                        {{-- Ön Yüz Mevcut --}}
                        @if($coin->hasMedia('obverse'))
                            <div>
                                <flux:text class="font-medium mb-2">{{ __('Mevcut Ön Yüz') }}</flux:text>
                                <div class="flex items-center gap-3">
                                    <img src="{{ $coin->getFirstMediaUrl('obverse', 'preview') }}" class="w-32 h-32 object-contain rounded-lg border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800 p-1" />
                                    <flux:button size="sm" variant="ghost" icon="trash" class="text-red-400"
                                        wire:click="removeMedia({{ $coin->getFirstMedia('obverse')->id }})"
                                        wire:confirm="{{ __('Bu görseli silmek istiyor musunuz?') }}">
                                        {{ __('Kaldır') }}
                                    </flux:button>
                                </div>
                            </div>
                        @endif

                        {{-- Arka Yüz Mevcut --}}
                        @if($coin->hasMedia('reverse'))
                            <div>
                                <flux:text class="font-medium mb-2">{{ __('Mevcut Arka Yüz') }}</flux:text>
                                <div class="flex items-center gap-3">
                                    <img src="{{ $coin->getFirstMediaUrl('reverse', 'preview') }}" class="w-32 h-32 object-contain rounded-lg border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800 p-1" />
                                    <flux:button size="sm" variant="ghost" icon="trash" class="text-red-400"
                                        wire:click="removeMedia({{ $coin->getFirstMedia('reverse')->id }})"
                                        wire:confirm="{{ __('Bu görseli silmek istiyor musunuz?') }}">
                                        {{ __('Kaldır') }}
                                    </flux:button>
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- Gallery --}}
                    @if($coin->hasMedia('gallery'))
                        <div>
                            <flux:text class="font-medium mb-2">{{ __('Mevcut Galeri') }}</flux:text>
                            <div class="flex flex-wrap gap-2">
                                @foreach($coin->getMedia('gallery') as $media)
                                    <div class="relative group">
                                        <img src="{{ $media->getUrl('thumb') }}" class="w-16 h-16 object-cover rounded-lg border border-zinc-200 dark:border-zinc-700" />
                                        <button type="button" wire:click="removeMedia({{ $media->id }})"
                                            class="absolute -top-1.5 -right-1.5 bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs opacity-0 group-hover:opacity-100 transition-opacity">
                                            ×
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Documents --}}
                    @if($coin->hasMedia('document'))
                        <div>
                            <flux:text class="font-medium mb-2">{{ __('Mevcut Belgeler') }}</flux:text>
                            <div class="space-y-1">
                                @foreach($coin->getMedia('document') as $media)
                                    <div class="flex items-center justify-between py-1 px-3 bg-zinc-50 dark:bg-zinc-800 rounded-lg">
                                        <div class="flex items-center gap-2 text-sm">
                                            <flux:icon icon="document" class="size-4 text-zinc-400" />
                                            <span>{{ $media->file_name }}</span>
                                            <span class="text-zinc-400">{{ number_format($media->size / 1024, 0) }} KB</span>
                                        </div>
                                        <flux:button size="sm" variant="ghost" icon="trash" class="text-red-400" wire:click="removeMedia({{ $media->id }})" />
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endisset

                {{-- Upload Alanları --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <flux:input
                            wire:model="{{ isset($coin) ? 'newObversePhoto' : 'obversePhoto' }}"
                            type="file" accept="image/*"
                            label="{{ isset($coin) && $coin->hasMedia('obverse') ? __('Ön Yüzü Değiştir') : __('Ön Yüz Görseli') }}"
                            description="JPG, PNG, WebP — maks. 10MB"
                        />
                        @php $obversePreview = isset($coin) ? ($newObversePhoto ?? null) : ($obversePhoto ?? null); @endphp
                        @if($obversePreview)
                            <img src="{{ $obversePreview->temporaryUrl() }}" class="mt-2 w-24 h-24 object-contain rounded-lg border bg-zinc-50 dark:bg-zinc-800 p-1" />
                        @endif
                    </div>
                    <div>
                        <flux:input
                            wire:model="{{ isset($coin) ? 'newReversePhoto' : 'reversePhoto' }}"
                            type="file" accept="image/*"
                            label="{{ isset($coin) && $coin->hasMedia('reverse') ? __('Arka Yüzü Değiştir') : __('Arka Yüz Görseli') }}"
                            description="JPG, PNG, WebP — maks. 10MB"
                        />
                        @php $reversePreview = isset($coin) ? ($newReversePhoto ?? null) : ($reversePhoto ?? null); @endphp
                        @if($reversePreview)
                            <img src="{{ $reversePreview->temporaryUrl() }}" class="mt-2 w-24 h-24 object-contain rounded-lg border bg-zinc-50 dark:bg-zinc-800 p-1" />
                        @endif
                    </div>
                </div>

                <flux:input
                    wire:model="{{ isset($coin) ? 'newGallery' : 'gallery' }}"
                    type="file" multiple accept="image/*"
                    label="{{ __('Galeri Fotoğrafları') }}"
                    description="{{ __('Birden fazla seçilebilir — maks. 10MB/dosya') }}"
                />
                <flux:input
                    wire:model="{{ isset($coin) ? 'newDocuments' : 'documents' }}"
                    type="file" multiple accept=".pdf,.svg,.png,.jpg,.jpeg"
                    label="{{ __('Belgeler & Çizimler') }}"
                    description="{{ __('PDF, SVG, PNG — maks. 20MB/dosya') }}"
                />
            </flux:card>
        </div>

        {{-- Kaydet / İptal --}}
        <div class="flex items-center justify-between pt-2">
            <a href="{{ ($find && $find->exists && $project && $project->exists) ? route('coins.index', [$project, $find]) : route('all-coins.index') }}" wire:navigate>
                <flux:button variant="ghost" icon="arrow-left">{{ __('Geri Dön') }}</flux:button>
            </a>
            <div class="flex gap-3">
                <flux:button type="submit" variant="primary" icon="check">
                    {{ isset($coin) ? __('Güncelle') : __('Kaydet') }}
                </flux:button>
            </div>
        </div>

    </form>
</div>
