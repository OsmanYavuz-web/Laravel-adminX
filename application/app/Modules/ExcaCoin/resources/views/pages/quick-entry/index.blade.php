<div class="max-w-5xl mx-auto space-y-6">

    {{-- Başlık ve Bilgilendirme --}}
    <div class="flex items-center justify-between">
        <div>
            <flux:heading size="xl" class="flex items-center gap-2">
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-amber-500/15 text-amber-600 dark:text-amber-400">
                    <flux:icon icon="bolt" class="size-5" />
                </span>
                {{ __('Hızlı Veri Girişi') }}
            </flux:heading>
            <flux:subheading>{{ __('Buluntu ve bağlı sikke kayıtlarını tek sayfada seri bir şekilde veritabanına ekleyin.') }}</flux:subheading>
        </div>
    </div>

    <form wire:submit="save" class="space-y-6" enctype="multipart/form-data">

        {{-- ───────────────────────────────────────────── --}}
        {{-- BÖLÜM 1: PROJE SEÇİMİ VE ZORUNLU BULUNTU      --}}
        {{-- ───────────────────────────────────────────── --}}
        <flux:card class="space-y-5 border-amber-500/30 dark:border-amber-500/20 shadow-md">
            <div class="flex items-center justify-between border-b border-zinc-200 dark:border-zinc-700 pb-3">
                <flux:heading size="md" class="text-amber-700 dark:text-amber-400 flex items-center gap-2">
                    <flux:icon icon="archive-box" class="size-5 text-amber-500" />
                    {{ __('1. Buluntu Bilgileri (Zorunlu)') }}
                </flux:heading>
                <span class="text-xs text-zinc-400 font-mono">* {{ __('Zorunlu alanlar') }}</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                {{-- Proje Seçimi --}}
                <flux:field class="sm:col-span-2">
                    <flux:label>
                        {{ __('Kazı Projesi') }} <span class="text-red-500 font-bold">*</span>
                    </flux:label>
                    <flux:select wire:model.live="projectId" x-searchable required>
                        <flux:select.option value="">{{ __('Proje Seçin...') }}</flux:select.option>
                        @foreach($this->accessibleProjects as $p)
                            <option value="{{ $p->id }}">{{ $p->name }} ({{ $p->site_name }})</option>
                        @endforeach
                    </flux:select>
                </flux:field>

                {{-- Buluntu Tarihi --}}
                <flux:field>
                    <flux:label>
                        {{ __('Buluntu Tarihi') }} <span class="text-red-500 font-bold">*</span>
                    </flux:label>
                    <flux:input wire:model="findDate" type="date" required />
                </flux:field>

                {{-- Envanter Numarası --}}
                <flux:field>
                    <flux:label>
                        {{ __('Envanter Numarası') }} <span class="text-red-500 font-bold">*</span>
                    </flux:label>
                    <flux:input wire:model="inventoryNumber" placeholder="2024-001" required />
                </flux:field>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                {{-- Kazı Alanı --}}
                <flux:field>
                    <flux:label>
                        {{ __('Kazı Alanı') }} <span class="text-red-500 font-bold">*</span>
                    </flux:label>
                    <flux:input wire:model="excavationArea" placeholder="{{ __('Kuzey Sektör') }}" required />
                </flux:field>
            </div>
        </flux:card>

        {{-- ───────────────────────────────────────────── --}}
        {{-- BULUNTU OPSİYONEL ALANLARI (Accordionlar)     --}}
        {{-- ───────────────────────────────────────────── --}}
        @php
            $proj = $this->selectedProject;
        @endphp

        {{-- Buluntu Bağlam Bilgileri --}}
        @if(!$proj || $proj->hasAnyFieldVisible(['excavation_season', 'sector', 'area', 'trench', 'square', 'sub_square', 'locus', 'context', 'stratigraphic_unit', 'unit', 'layer', 'level', 'phase', 'feature', 'grave_number', 'structure', 'room', 'architectural_feature']))
            <div x-data="{ open: false }">
                <button type="button" @click="open = !open"
                    class="w-full flex items-center justify-between px-5 py-3 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-xl text-left shadow-xs">
                    <div class="flex items-center gap-2 font-medium text-zinc-700 dark:text-zinc-300">
                        <flux:icon icon="squares-2x2" class="size-4 text-zinc-500" />
                        <span>{{ __('Buluntu Bağlam Bilgileri') }}</span>
                        <span class="text-xs text-zinc-400">({{ __('opsiyonel') }})</span>
                    </div>
                    <flux:icon icon="chevron-down" class="size-4 text-zinc-400 transition-transform" ::class="open ? 'rotate-180' : ''" />
                </button>
                <div x-show="open" x-collapse class="mt-1">
                    <flux:card class="space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            @if(!$proj || $proj->isFieldVisible('excavation_season')) <flux:input wire:model="excavationSeason" label="{{ __('Kazı Sezonu') }}" placeholder="2024" /> @endif
                            @if(!$proj || $proj->isFieldVisible('sector')) <flux:input wire:model="sector" label="{{ __('Sektör') }}" /> @endif
                            @if(!$proj || $proj->isFieldVisible('area')) <flux:input wire:model="area" label="{{ __('Alan') }}" /> @endif
                            @if(!$proj || $proj->isFieldVisible('trench')) <flux:input wire:model="trench" label="{{ __('Açma') }}" /> @endif
                            @if(!$proj || $proj->isFieldVisible('square')) <flux:input wire:model="square" label="{{ __('Kare / Grid') }}" /> @endif
                            @if(!$proj || $proj->isFieldVisible('sub_square')) <flux:input wire:model="subSquare" label="{{ __('Alt Kare') }}" /> @endif
                            @if(!$proj || $proj->isFieldVisible('locus')) <flux:input wire:model="locus" label="{{ __('Locus') }}" /> @endif
                            @if(!$proj || $proj->isFieldVisible('context')) <flux:input wire:model="context" label="{{ __('Konteks') }}" /> @endif
                            @if(!$proj || $proj->isFieldVisible('stratigraphic_unit')) <flux:input wire:model="stratigraphicUnit" label="{{ __('Stratigrafik Birim (SU)') }}" /> @endif
                            @if(!$proj || $proj->isFieldVisible('unit')) <flux:input wire:model="unit" label="{{ __('Unit / Birim') }}" /> @endif
                            @if(!$proj || $proj->isFieldVisible('layer')) <flux:input wire:model="layer" label="{{ __('Tabaka') }}" /> @endif
                            @if(!$proj || $proj->isFieldVisible('level')) <flux:input wire:model="level" label="{{ __('Seviye') }}" /> @endif
                            @if(!$proj || $proj->isFieldVisible('phase')) <flux:input wire:model="phase" label="{{ __('Evre') }}" /> @endif
                            @if(!$proj || $proj->isFieldVisible('feature')) <flux:input wire:model="feature" label="{{ __('Feature / Özellik') }}" /> @endif
                            @if(!$proj || $proj->isFieldVisible('grave_number')) <flux:input wire:model="graveNumber" label="{{ __('Mezar Numarası') }}" /> @endif
                            @if(!$proj || $proj->isFieldVisible('structure')) <flux:input wire:model="structure" label="{{ __('Yapı') }}" /> @endif
                            @if(!$proj || $proj->isFieldVisible('room')) <flux:input wire:model="room" label="{{ __('Mekân') }}" /> @endif
                            @if(!$proj || $proj->isFieldVisible('architectural_feature')) <flux:input wire:model="architecturalFeature" label="{{ __('Mimari Unsur') }}" /> @endif
                        </div>
                    </flux:card>
                </div>
            </div>
        @endif

        {{-- Buluntu Konum & Ek Bilgiler --}}
        @if(!$proj || $proj->hasAnyFieldVisible(['find_spot', 'elevation', 'coordinate_x', 'coordinate_y', 'coordinate_z', 'find_number', 'bag_number', 'find_group', 'find_note']))
            <div x-data="{ open: false }">
                <button type="button" @click="open = !open"
                    class="w-full flex items-center justify-between px-5 py-3 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-xl text-left shadow-xs">
                    <div class="flex items-center gap-2 font-medium text-zinc-700 dark:text-zinc-300">
                        <flux:icon icon="map-pin" class="size-4 text-zinc-500" />
                        <span>{{ __('Buluntu Konum & Ek Bilgiler') }}</span>
                        <span class="text-xs text-zinc-400">({{ __('opsiyonel') }})</span>
                    </div>
                    <flux:icon icon="chevron-down" class="size-4 text-zinc-400 transition-transform" ::class="open ? 'rotate-180' : ''" />
                </button>
                <div x-show="open" x-collapse class="mt-1">
                    <flux:card class="space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            @if(!$proj || $proj->isFieldVisible('find_spot')) <flux:input wire:model="findSpot" label="{{ __('Buluntu Yeri') }}" /> @endif
                            @if(!$proj || $proj->isFieldVisible('elevation')) <flux:input wire:model="elevation" type="number" step="0.01" label="{{ __('Kot (m)') }}" /> @endif
                            @if(!$proj || $proj->isFieldVisible('coordinate_x')) <flux:input wire:model="coordinateX" type="number" step="0.0001" label="{{ __('Koordinat X') }}" /> @endif
                            @if(!$proj || $proj->isFieldVisible('coordinate_y')) <flux:input wire:model="coordinateY" type="number" step="0.0001" label="{{ __('Koordinat Y') }}" /> @endif
                            @if(!$proj || $proj->isFieldVisible('coordinate_z')) <flux:input wire:model="coordinateZ" type="number" step="0.0001" label="{{ __('Koordinat Z') }}" /> @endif
                            @if(!$proj || $proj->isFieldVisible('find_number')) <flux:input wire:model="findNumber" label="{{ __('Buluntu No') }}" /> @endif
                            @if(!$proj || $proj->isFieldVisible('bag_number')) <flux:input wire:model="bagNumber" label="{{ __('Torba No') }}" /> @endif
                            @if(!$proj || $proj->isFieldVisible('find_group')) <flux:input wire:model="findGroup" label="{{ __('Buluntu Grubu') }}" /> @endif
                        </div>
                        @if(!$proj || $proj->isFieldVisible('find_note'))
                            <flux:textarea wire:model="findNote" label="{{ __('Buluntu Notu') }}" rows="2" />
                        @endif
                    </flux:card>
                </div>
            </div>
        @endif

        {{-- ───────────────────────────────────────────── --}}
        {{-- BÖLÜM 2: SİKKE DETAYLARI BİLGİLERİ            --}}
        {{-- ───────────────────────────────────────────── --}}
        <div class="bg-gradient-to-r from-amber-500/10 via-amber-500/5 to-transparent p-5 rounded-2xl border border-amber-500/30 space-y-6">
            <div class="flex items-center gap-3 border-b border-amber-500/20 pb-4">
                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-500 text-white shadow-sm">
                    <flux:icon icon="circle-stack" class="size-6" />
                </span>
                <div>
                    <h3 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">{{ __('2. Sikke Detayları Bilgileri') }}</h3>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('Buluntuya ait nümismatik sikke detaylarını girin.') }}</p>
                </div>
            </div>

            <div class="space-y-6" x-data="{ coinTab: @entangle('coinActiveTab') }">

                    {{-- Sikke Sekmeleri --}}
                    @php
                        $tabFieldMap = [
                            'identification' => ['period_id', 'authority_id', 'ruler_id', 'region_id', 'mint_id', 'denomination_id', 'date_range'],
                            'physical'       => ['metal_id', 'diameter', 'weight', 'axis', 'is_cut', 'is_pierced'],
                            'obverse'        => ['obverse_legend', 'obverse_legend_expanded', 'obverse_description'],
                            'reverse'        => ['reverse_legend', 'reverse_legend_expanded', 'reverse_description'],
                            'extra'          => ['mint_mark', 'magistrate', 'control_mark', 'monogram', 'countermark', 'reference', 'note', 'is_overstrike'],
                            'media'          => ['coin_photos'],
                        ];
                    @endphp
                    <div class="flex flex-wrap gap-1 bg-white dark:bg-zinc-800 p-1.5 rounded-xl border border-zinc-200 dark:border-zinc-700 shadow-xs">
                        @foreach([
                            ['id' => 'identification', 'label' => __('Tanımlama'),    'icon' => 'tag'],
                            ['id' => 'physical',      'label' => __('Fiziksel'),      'icon' => 'scale'],
                            ['id' => 'obverse',       'label' => __('Ön Yüz'),       'icon' => 'sun'],
                            ['id' => 'reverse',       'label' => __('Arka Yüz'),     'icon' => 'moon'],
                            ['id' => 'extra',         'label' => __('Ekstra & Not'),  'icon' => 'ellipsis-horizontal-circle'],
                            ['id' => 'media',         'label' => __('Görseller'),     'icon' => 'photo'],
                        ] as $tab)
                            @if(empty($tabFieldMap[$tab['id']]) || !$proj || $proj->hasAnyFieldVisible($tabFieldMap[$tab['id']]))
                                <button type="button" @click="coinTab = '{{ $tab['id'] }}'"
                                    :class="coinTab === '{{ $tab['id'] }}' ? 'bg-amber-500 text-white font-medium shadow-xs' : 'text-zinc-600 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-700'"
                                    class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs transition-all">
                                    <flux:icon icon="{{ $tab['icon'] }}" class="size-3.5" />
                                    {{ $tab['label'] }}
                                </button>
                            @endif
                        @endforeach
                    </div>

                    {{-- 1. TANIMLAMA --}}
                    <div x-show="coinTab === 'identification'" class="space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                            @if(!$proj || $proj->isFieldVisible('period_id'))
                                <flux:select wire:model="periodId" label="{{ __('Dönem') }}" x-searchable>
                                    <flux:select.option value="">— {{ __('Seçilmedi') }} —</flux:select.option>
                                    @foreach($this->periods as $item)
                                        <flux:select.option value="{{ $item->id }}">{{ $item->getTranslation('name', app()->getLocale(), false) }}</flux:select.option>
                                    @endforeach
                                </flux:select>
                            @endif

                            @if(!$proj || $proj->isFieldVisible('authority_id'))
                                <flux:select wire:model="authorityId" label="{{ __('Otorite / Devlet') }}" x-searchable>
                                    <flux:select.option value="">— {{ __('Seçilmedi') }} —</flux:select.option>
                                    @foreach($this->authorities as $item)
                                        <flux:select.option value="{{ $item->id }}">{{ $item->getTranslation('name', app()->getLocale(), false) }}</flux:select.option>
                                    @endforeach
                                </flux:select>
                            @endif

                            @if(!$proj || $proj->isFieldVisible('ruler_id'))
                                <flux:select wire:model="rulerId" label="{{ __('Hükümdar / İmparator') }}" x-searchable>
                                    <flux:select.option value="">— {{ __('Seçilmedi') }} —</flux:select.option>
                                    @foreach($this->rulers as $item)
                                        <flux:select.option value="{{ $item->id }}">{{ $item->getTranslation('name', app()->getLocale(), false) }}</flux:select.option>
                                    @endforeach
                                </flux:select>
                            @endif

                            @if(!$proj || $proj->isFieldVisible('region_id'))
                                <flux:select wire:model="regionId" label="{{ __('Bölge') }}" x-searchable>
                                    <flux:select.option value="">— {{ __('Seçilmedi') }} —</flux:select.option>
                                    @foreach($this->regions as $item)
                                        <flux:select.option value="{{ $item->id }}">{{ $item->getTranslation('name', app()->getLocale(), false) }}</flux:select.option>
                                    @endforeach
                                </flux:select>
                            @endif

                            @if(!$proj || $proj->isFieldVisible('mint_id'))
                                <flux:select wire:model="mintId" label="{{ __('Darphane') }}" x-searchable>
                                    <flux:select.option value="">— {{ __('Seçilmedi') }} —</flux:select.option>
                                    @foreach($this->mints as $item)
                                        <flux:select.option value="{{ $item->id }}">{{ $item->getTranslation('name', app()->getLocale(), false) }}</flux:select.option>
                                    @endforeach
                                </flux:select>
                            @endif

                            @if(!$proj || $proj->isFieldVisible('denomination_id'))
                                <flux:select wire:model="denominationId" label="{{ __('Nominal / Birim') }}" x-searchable>
                                    <flux:select.option value="">— {{ __('Seçilmedi') }} —</flux:select.option>
                                    @foreach($this->denominations as $item)
                                        <flux:select.option value="{{ $item->id }}">{{ $item->getTranslation('name', app()->getLocale(), false) }}</flux:select.option>
                                    @endforeach
                                </flux:select>
                            @endif

                            @if(!$proj || $proj->isFieldVisible('date_range'))
                                <flux:input wire:model="dateRange" label="{{ __('Tarih Aralığı') }}" placeholder="{{ __('MÖ 200 - MÖ 150') }}" />
                            @endif
                        </div>
                    </div>

                    {{-- 2. FİZİKSEL --}}
                    <div x-show="coinTab === 'physical'" class="space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                            @if(!$proj || $proj->isFieldVisible('metal_id'))
                                <flux:select wire:model="metalId" label="{{ __('Metal') }}" x-searchable>
                                    <flux:select.option value="">— {{ __('Seçilmedi') }} —</flux:select.option>
                                    @foreach($this->metals as $item)
                                        <flux:select.option value="{{ $item->id }}">{{ $item->getTranslation('name', app()->getLocale(), false) }}</flux:select.option>
                                    @endforeach
                                </flux:select>
                            @endif

                            @if(!$proj || $proj->isFieldVisible('diameter'))
                                <flux:input wire:model="diameter" type="number" step="0.01" label="{{ __('Çap (mm)') }}" placeholder="18.5" />
                            @endif

                            @if(!$proj || $proj->isFieldVisible('weight'))
                                <flux:input wire:model="weight" type="number" step="0.001" label="{{ __('Ağırlık (g)') }}" placeholder="4.25" />
                            @endif

                            @if(!$proj || $proj->isFieldVisible('axis'))
                                <flux:input wire:model="axis" type="number" min="1" max="12" label="{{ __('Kalıp Yönü (Saat 1-12)') }}" placeholder="12" />
                            @endif
                        </div>
                        <div class="flex gap-6 pt-2">
                            @if(!$proj || $proj->isFieldVisible('is_cut')) <flux:checkbox wire:model="isCut" label="{{ __('Kesilmiş / Kırpılmış') }}" /> @endif
                            @if(!$proj || $proj->isFieldVisible('is_pierced')) <flux:checkbox wire:model="isPierced" label="{{ __('Delinmiş') }}" /> @endif
                        </div>
                    </div>

                    {{-- 3. ÖN YÜZ --}}
                    <div x-show="coinTab === 'obverse'" class="space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            @if(!$proj || $proj->isFieldVisible('obverse_legend')) <flux:input wire:model="obverseLegend" label="{{ __('Ön Yüz Yazısı (Legend)') }}" /> @endif
                            @if(!$proj || $proj->isFieldVisible('obverse_legend_expanded')) <flux:input wire:model="obverseLegendExpanded" label="{{ __('Yazı Açılımı') }}" /> @endif
                        </div>
                        @if(!$proj || $proj->isFieldVisible('obverse_description'))
                            <flux:textarea wire:model="obverseDescription" label="{{ __('Ön Yüz Tasviri / Açıklaması') }}" rows="3" />
                        @endif
                    </div>

                    {{-- 4. ARKA YÜZ --}}
                    <div x-show="coinTab === 'reverse'" class="space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            @if(!$proj || $proj->isFieldVisible('reverse_legend')) <flux:input wire:model="reverseLegend" label="{{ __('Arka Yüz Yazısı (Legend)') }}" /> @endif
                            @if(!$proj || $proj->isFieldVisible('reverse_legend_expanded')) <flux:input wire:model="reverseLegendExpanded" label="{{ __('Yazı Açılımı') }}" /> @endif
                        </div>
                        @if(!$proj || $proj->isFieldVisible('reverse_description'))
                            <flux:textarea wire:model="reverseDescription" label="{{ __('Arka Yüz Tasviri / Açıklaması') }}" rows="3" />
                        @endif
                    </div>

                    {{-- 5. EKSTRA & NOT --}}
                    <div x-show="coinTab === 'extra'" class="space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            @if(!$proj || $proj->isFieldVisible('mint_mark')) <flux:input wire:model="mintMark" label="{{ __('Darphane İşareti') }}" /> @endif
                            @if(!$proj || $proj->isFieldVisible('magistrate')) <flux:input wire:model="magistrate" label="{{ __('Magistrat / Yetkili') }}" /> @endif
                            @if(!$proj || $proj->isFieldVisible('control_mark')) <flux:input wire:model="controlMark" label="{{ __('Kontrol İşareti') }}" /> @endif
                            @if(!$proj || $proj->isFieldVisible('monogram')) <flux:input wire:model="monogram" label="{{ __('Monogram') }}" /> @endif
                            @if(!$proj || $proj->isFieldVisible('countermark')) <flux:input wire:model="countermark" label="{{ __('Kontrmark') }}" /> @endif
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            @if(!$proj || $proj->isFieldVisible('reference')) <flux:textarea wire:model="reference" label="{{ __('Katalog Referansı') }}" rows="2" placeholder="RIC VI, p. 120, no. 45" /> @endif
                            @if(!$proj || $proj->isFieldVisible('note')) <flux:textarea wire:model="note" label="{{ __('Notlar / Kondisyon') }}" rows="2" /> @endif
                        </div>
                        @if(!$proj || $proj->isFieldVisible('is_overstrike'))
                            <flux:checkbox wire:model="isOverstrike" label="{{ __('Üst Baskı (Overstrike)') }}" />
                        @endif
                    </div>

                    {{-- 6. GÖRSELLER --}}
                    <div x-show="coinTab === 'media'" class="space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <flux:input wire:model.live="coinObversePhoto" type="file" label="{{ __('Sikke Ön Yüz Fotoğrafı') }}" accept="image/*" />
                                @if($coinObversePhoto)
                                    <div class="mt-2 p-2 bg-emerald-500/10 border border-emerald-500/30 rounded-xl flex items-center justify-between">
                                        <div class="flex items-center gap-3">
                                            @if(method_exists($coinObversePhoto, 'temporaryUrl'))
                                                <img src="{{ $coinObversePhoto->temporaryUrl() }}" class="w-14 h-14 object-cover rounded-lg shadow-xs" />
                                            @endif
                                            <div>
                                                <span class="text-xs font-semibold text-emerald-700 dark:text-emerald-300 block">{{ __('Ön Yüz Fotoğrafı Seçildi') }}</span>
                                                <span class="text-[11px] text-zinc-500 dark:text-zinc-400 block truncate max-w-[200px]">{{ $coinObversePhoto->getClientOriginalName() }}</span>
                                            </div>
                                        </div>
                                        <flux:button type="button" size="sm" variant="ghost" icon="x-mark" wire:click="$set('coinObversePhoto', null)" class="text-red-500 hover:text-red-700" title="{{ __('Kaldır') }}" />
                                    </div>
                                @endif
                            </div>

                            <div>
                                <flux:input wire:model.live="coinReversePhoto" type="file" label="{{ __('Sikke Arka Yüz Fotoğrafı') }}" accept="image/*" />
                                @if($coinReversePhoto)
                                    <div class="mt-2 p-2 bg-emerald-500/10 border border-emerald-500/30 rounded-xl flex items-center justify-between">
                                        <div class="flex items-center gap-3">
                                            @if(method_exists($coinReversePhoto, 'temporaryUrl'))
                                                <img src="{{ $coinReversePhoto->temporaryUrl() }}" class="w-14 h-14 object-cover rounded-lg shadow-xs" />
                                            @endif
                                            <div>
                                                <span class="text-xs font-semibold text-emerald-700 dark:text-emerald-300 block">{{ __('Arka Yüz Fotoğrafı Seçildi') }}</span>
                                                <span class="text-[11px] text-zinc-500 dark:text-zinc-400 block truncate max-w-[200px]">{{ $coinReversePhoto->getClientOriginalName() }}</span>
                                            </div>
                                        </div>
                                        <flux:button type="button" size="sm" variant="ghost" icon="x-mark" wire:click="$set('coinReversePhoto', null)" class="text-red-500 hover:text-red-700" title="{{ __('Kaldır') }}" />
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                </div>
        </div>

        {{-- ───────────────────────────────────────────── --}}
        {{-- KAYDET VE TEMİZLE BUTONU                      --}}
        {{-- ───────────────────────────────────────────── --}}
        <div class="flex items-center justify-between pt-4 border-t border-zinc-200 dark:border-zinc-700">
            <flux:button type="button" variant="ghost" wire:click="$set('showResetModal', true)" icon="arrow-path">
                {{ __('Formu Sıfırla') }}
            </flux:button>
            <flux:button type="submit" variant="primary" icon="bolt" class="bg-amber-600 hover:bg-amber-700 text-white px-8 py-2.5">
                {{ __('Hızlı Kaydet & Yeni Ekle') }}
            </flux:button>
        </div>

    </form>

    {{-- Form Sıfırlama Onay Modalı (Flux UI) --}}
    <flux:modal wire:model="showResetModal" class="max-w-md p-5">
        <div class="space-y-5">
            <div class="flex items-start gap-4">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-amber-500/15 text-amber-600 dark:text-amber-400">
                    <flux:icon icon="exclamation-triangle" class="size-6" />
                </div>
                <div>
                    <flux:heading size="lg" class="font-extrabold text-zinc-900 dark:text-white">
                        {{ __('Formu Sıfırla?') }}
                    </flux:heading>
                    <flux:subheading class="text-xs text-zinc-500 mt-1">
                        {{ __('Formdaki girilmiş tüm veriler temizlenecektir. Devam etmek istediğinizden emin misiniz?') }}
                    </flux:subheading>
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <flux:button variant="ghost" wire:click="$set('showResetModal', false)">
                    {{ __('Vazgeç') }}
                </flux:button>
                <flux:button variant="primary" class="bg-amber-600 hover:bg-amber-700 text-white" wire:click="resetForm">
                    {{ __('Evet, Sıfırla') }}
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
