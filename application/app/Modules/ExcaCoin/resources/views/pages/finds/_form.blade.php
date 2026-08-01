<div class="max-w-5xl mx-auto space-y-6">

    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 text-sm text-zinc-400">
        <a href="{{ route('excavation-projects.index') }}" wire:navigate class="hover:text-zinc-600 dark:hover:text-zinc-200">{{ __('Kazı Projeleri') }}</a>
        @if($project && $project->exists)
            <flux:icon icon="chevron-right" class="size-4" />
            <a href="{{ route('finds.index', $project) }}" wire:navigate class="hover:text-zinc-600 dark:hover:text-zinc-200">{{ $project->name }}</a>
        @endif
        <flux:icon icon="chevron-right" class="size-4" />
        <span class="text-zinc-600 dark:text-zinc-300">
            {{ isset($find) ? $find->inventory_number : __('Yeni Buluntu') }}
        </span>
    </div>

    <flux:heading size="xl">
        {{ isset($find) ? __('Buluntu Düzenle') : __('Yeni Buluntu') }}
    </flux:heading>

    <form wire:submit="save" class="space-y-5" enctype="multipart/form-data">

        {{-- ───────────────────────────────────────────── --}}
        {{-- BÖLÜM 1: ZORUNLU / TEMEL BİLGİLER            --}}
        {{-- ───────────────────────────────────────────── --}}
        <flux:card class="space-y-4">
            <flux:heading size="md" class="text-zinc-700 dark:text-zinc-300">
                <flux:icon icon="identification" class="inline size-4 mr-1" />
                {{ __('Temel Bilgiler') }}
            </flux:heading>

            @if(!$project || !$project->exists)
                <flux:field>
                    <flux:label>
                        {{ __('Kazı Projesi') }} <span class="ml-1 text-red-500 font-bold" title="{{ __('Zorunlu alan') }}">*</span>
                        <svg class="inline size-3.5 text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200 cursor-help ml-1" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><title>{{ __('Buluntunun ait olduğu kazı projesi') }}</title><path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" /></svg>
                    </flux:label>
                    <flux:select wire:model.live="projectId" x-searchable required>
                        <option value="">{{ __('Proje Seçin...') }}</option>
                        @foreach(\App\Modules\ExcaCoin\Models\ExcavationProject::accessibleBy()->get() as $p)
                            <option value="{{ $p->id }}">{{ $p->name }} ({{ $p->site_name }})</option>
                        @endforeach
                    </flux:select>
                </flux:field>
            @endif

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <flux:field>
                    <flux:label>
                        {{ __('Buluntu Tarihi') }} <span class="ml-1 text-red-500 font-bold" title="{{ __('Zorunlu alan') }}">*</span>
                        <svg class="inline size-3.5 text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200 cursor-help ml-1" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><title>{{ __('Eserin gün yüzüne çıkarıldığı tarih') }}</title><path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" /></svg>
                    </flux:label>
                    <flux:input
                        wire:model="findDate"
                        type="date"
                        required
                    />
                </flux:field>
                <flux:field>
                    <flux:label>
                        {{ __('Envanter Numarası') }} <span class="ml-1 text-red-500 font-bold" title="{{ __('Zorunlu alan') }}">*</span>
                        <svg class="inline size-3.5 text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200 cursor-help ml-1" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><title>{{ __('Kazı envanter kayıt numarası (Örn: 2024-001)') }}</title><path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" /></svg>
                    </flux:label>
                    <flux:input
                        wire:model="inventoryNumber"
                        placeholder="{{ __('2024-001') }}"
                        required
                    />
                </flux:field>
                <flux:field>
                    <flux:label>
                        {{ __('Kazı Alanı') }} <span class="ml-1 text-red-500 font-bold" title="{{ __('Zorunlu alan') }}">*</span>
                        <svg class="inline size-3.5 text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200 cursor-help ml-1" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><title>{{ __('Eserin bulunduğu sektör veya alan adı') }}</title><path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" /></svg>
                    </flux:label>
                    <flux:input
                        wire:model="excavationArea"
                        placeholder="{{ __('Kuzey Sektör') }}"
                        required
                    />
                </flux:field>
            </div>
           @php
            $activeProject = $project && $project->exists ? $project : ($projectId ? \App\Modules\ExcaCoin\Models\ExcavationProject::find($projectId) : null);
        @endphp

        @if(!$activeProject || $activeProject->hasAnyFieldVisible(['excavation_season', 'sector', 'area', 'trench', 'square', 'sub_square', 'locus', 'context', 'stratigraphic_unit', 'unit', 'layer', 'level', 'phase', 'feature', 'grave_number', 'structure', 'room', 'architectural_feature']))
            <div x-data="{ open: {{ $errors->hasAny(['sector','area','trench','square','subSquare','locus','context','stratigraphicUnit','unit','layer','level','phase','feature','graveNumber','structure','room','architecturalFeature','excavationSeason']) ? 'true' : 'false' }} }">
                <button type="button" @click="open = !open"
                    class="w-full flex items-center justify-between px-5 py-3.5 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-xl text-left shadow-sm">
                    <div class="flex items-center gap-2 font-medium text-zinc-700 dark:text-zinc-300">
                        <flux:icon icon="squares-2x2" class="size-4" />
                        {{ __('Bağlam Bilgileri') }}
                        <span class="text-xs text-zinc-400 font-normal">{{ __('(opsiyonel)') }}</span>
                    </div>
                    <flux:icon icon="chevron-down" class="size-4 text-zinc-400 transition-transform" ::class="open ? 'rotate-180' : ''" />
                </button>
                <div x-show="open" x-collapse class="mt-1">
                    <flux:card class="space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                            @if(!$activeProject || $activeProject->isFieldVisible('excavation_season')) <flux:input wire:model="excavationSeason" label="{{ __('Kazı Sezonu') }}" placeholder="{{ __('2024') }}" /> @endif
                            @if(!$activeProject || $activeProject->isFieldVisible('sector')) <flux:input wire:model="sector" label="{{ __('Sektör') }}" placeholder="{{ __('A') }}" /> @endif
                            @if(!$activeProject || $activeProject->isFieldVisible('area')) <flux:input wire:model="area" label="{{ __('Alan') }}" /> @endif
                            @if(!$activeProject || $activeProject->isFieldVisible('trench')) <flux:input wire:model="trench" label="{{ __('Açma') }}" /> @endif
                            @if(!$activeProject || $activeProject->isFieldVisible('square')) <flux:input wire:model="square" label="{{ __('Kare / Grid') }}" /> @endif
                            @if(!$activeProject || $activeProject->isFieldVisible('sub_square')) <flux:input wire:model="subSquare" label="{{ __('Alt Kare') }}" /> @endif
                            @if(!$activeProject || $activeProject->isFieldVisible('locus')) <flux:input wire:model="locus" label="{{ __('Locus') }}" /> @endif
                            @if(!$activeProject || $activeProject->isFieldVisible('context')) <flux:input wire:model="context" label="{{ __('Konteks') }}" /> @endif
                            @if(!$activeProject || $activeProject->isFieldVisible('stratigraphic_unit')) <flux:input wire:model="stratigraphicUnit" label="{{ __('Stratigrafik Birim (SU)') }}" /> @endif
                            @if(!$activeProject || $activeProject->isFieldVisible('unit')) <flux:input wire:model="unit" label="{{ __('Unit / Birim') }}" /> @endif
                            @if(!$activeProject || $activeProject->isFieldVisible('layer')) <flux:input wire:model="layer" label="{{ __('Tabaka') }}" /> @endif
                            @if(!$activeProject || $activeProject->isFieldVisible('level')) <flux:input wire:model="level" label="{{ __('Seviye') }}" /> @endif
                            @if(!$activeProject || $activeProject->isFieldVisible('phase')) <flux:input wire:model="phase" label="{{ __('Evre') }}" /> @endif
                            @if(!$activeProject || $activeProject->isFieldVisible('feature')) <flux:input wire:model="feature" label="{{ __('Feature / Özellik') }}" /> @endif
                            @if(!$activeProject || $activeProject->isFieldVisible('grave_number')) <flux:input wire:model="graveNumber" label="{{ __('Mezar Numarası') }}" /> @endif
                            @if(!$activeProject || $activeProject->isFieldVisible('structure')) <flux:input wire:model="structure" label="{{ __('Yapı') }}" /> @endif
                            @if(!$activeProject || $activeProject->isFieldVisible('room')) <flux:input wire:model="room" label="{{ __('Mekân') }}" /> @endif
                            @if(!$activeProject || $activeProject->isFieldVisible('architectural_feature')) <flux:input wire:model="architecturalFeature" label="{{ __('Mimari Unsur') }}" placeholder="{{ __('Duvar, taban, ocak vb.') }}" /> @endif
                        </div>
                    </flux:card>
                </div>
            </div>
        @endif

        {{-- ───────────────────────────────────────────── --}}
        {{-- BÖLÜM 3: KONUM BİLGİLERİ (accordion)         --}}
        {{-- ───────────────────────────────────────────── --}}
        @if(!$activeProject || $activeProject->hasAnyFieldVisible(['find_spot', 'elevation', 'coordinate_x', 'coordinate_y', 'coordinate_z']))
            <div x-data="{ open: {{ $errors->hasAny(['findSpot','elevation','coordinateX','coordinateY','coordinateZ']) ? 'true' : 'false' }} }">
                <button type="button" @click="open = !open"
                    class="w-full flex items-center justify-between px-5 py-3.5 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-xl text-left shadow-sm">
                    <div class="flex items-center gap-2 font-medium text-zinc-700 dark:text-zinc-300">
                        <flux:icon icon="map-pin" class="size-4" />
                        {{ __('Konum Bilgileri') }}
                        <span class="text-xs text-zinc-400 font-normal">{{ __('(opsiyonel)') }}</span>
                    </div>
                    <flux:icon icon="chevron-down" class="size-4 text-zinc-400 transition-transform" ::class="open ? 'rotate-180' : ''" />
                </button>
                <div x-show="open" x-collapse class="mt-1">
                    <flux:card class="space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            @if(!$activeProject || $activeProject->isFieldVisible('find_spot')) <flux:input wire:model="findSpot" label="{{ __('Buluntu Yeri') }}" placeholder="{{ __('Spesifik konum açıklaması') }}" /> @endif
                            @if(!$activeProject || $activeProject->isFieldVisible('elevation')) <flux:input wire:model="elevation" type="number" step="0.01" label="{{ __('Kot (metre)') }}" placeholder="0.00" /> @endif
                        </div>
                        <div class="grid grid-cols-3 gap-4">
                            @if(!$activeProject || $activeProject->isFieldVisible('coordinate_x')) <flux:input wire:model="coordinateX" type="number" step="0.0001" label="{{ __('Koordinat X') }}" /> @endif
                            @if(!$activeProject || $activeProject->isFieldVisible('coordinate_y')) <flux:input wire:model="coordinateY" type="number" step="0.0001" label="{{ __('Koordinat Y') }}" /> @endif
                            @if(!$activeProject || $activeProject->isFieldVisible('coordinate_z')) <flux:input wire:model="coordinateZ" type="number" step="0.0001" label="{{ __('Koordinat Z') }}" /> @endif
                        </div>
                    </flux:card>
                </div>
            </div>
        @endif

        {{-- ───────────────────────────────────────────── --}}
        {{-- BÖLÜM 4: EK BİLGİLER (accordion)             --}}
        {{-- ───────────────────────────────────────────── --}}
        @if(!$activeProject || $activeProject->hasAnyFieldVisible(['find_number', 'bag_number', 'find_group', 'find_note']))
            <div x-data="{ open: {{ $errors->hasAny(['findNumber','bagNumber','findGroup','findNote']) ? 'true' : 'false' }} }">
                <button type="button" @click="open = !open"
                    class="w-full flex items-center justify-between px-5 py-3.5 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-xl text-left shadow-sm">
                    <div class="flex items-center gap-2 font-medium text-zinc-700 dark:text-zinc-300">
                        <flux:icon icon="document-text" class="size-4" />
                        {{ __('Ek Bilgiler') }}
                        <span class="text-xs text-zinc-400 font-normal">{{ __('(opsiyonel)') }}</span>
                    </div>
                    <flux:icon icon="chevron-down" class="size-4 text-zinc-400 transition-transform" ::class="open ? 'rotate-180' : ''" />
                </button>
                <div x-show="open" x-collapse class="mt-1">
                    <flux:card class="space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            @if(!$activeProject || $activeProject->isFieldVisible('find_number')) <flux:input wire:model="findNumber" label="{{ __('Buluntu Numarası') }}" /> @endif
                            @if(!$activeProject || $activeProject->isFieldVisible('bag_number')) <flux:input wire:model="bagNumber" label="{{ __('Torba Numarası') }}" /> @endif
                            @if(!$activeProject || $activeProject->isFieldVisible('find_group')) <flux:input wire:model="findGroup" label="{{ __('Buluntu Grubu') }}" /> @endif
                        </div>
                        @if(!$activeProject || $activeProject->isFieldVisible('find_note'))
                            <flux:textarea wire:model="findNote" label="{{ __('Buluntu Notu') }}" rows="3"
                                placeholder="{{ __('Serbest açıklama alanı...') }}" />
                        @endif
                    </flux:card>
                </div>
            </div>
        @endif

        {{-- ───────────────────────────────────────────── --}}
        {{-- BÖLÜM 5: MEDYA                               --}}
        {{-- ───────────────────────────────────────────── --}}
        @if(!$activeProject || $activeProject->hasAnyFieldVisible(['cover_photo', 'gallery', 'documents']))
            <div x-data="{ open: false }">
                <button type="button" @click="open = !open"
                    class="w-full flex items-center justify-between px-5 py-3.5 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-xl text-left shadow-sm">
                    <div class="flex items-center gap-2 font-medium text-zinc-700 dark:text-zinc-300">
                        <flux:icon icon="photo" class="size-4" />
                        {{ __('Fotoğraflar & Belgeler') }}
                    </div>
                    <flux:icon icon="chevron-down" class="size-4 text-zinc-400 transition-transform" ::class="open ? 'rotate-180' : ''" />
                </button>
                <div x-show="open" x-collapse class="mt-1">
                    <flux:card class="space-y-6">

                        {{-- Mevcut medyayı göster (edit modunda) --}}
                        @isset($find)
                            {{-- Cover --}}
                            @if($find->hasMedia('cover'))
                                <div>
                                    <flux:text class="font-medium mb-2">{{ __('Mevcut Ana Görsel') }}</flux:text>
                                    <div class="flex items-center gap-3">
                                        <img src="{{ $find->getFirstMediaUrl('cover', 'thumb') }}" class="w-20 h-20 object-cover rounded-lg border border-zinc-200 dark:border-zinc-700" />
                                        <flux:button size="sm" variant="ghost" icon="trash" class="text-red-400"
                                            wire:click="removeMedia({{ $find->getFirstMedia('cover')->id }})"
                                            wire:confirm="{{ __('Bu görseli silmek istiyor musunuz?') }}">
                                            {{ __('Kaldır') }}
                                        </flux:button>
                                    </div>
                                </div>
                            @endif

                            {{-- Gallery --}}
                            @if($find->hasMedia('gallery'))
                                <div>
                                    <flux:text class="font-medium mb-2">{{ __('Mevcut Galeri') }}</flux:text>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($find->getMedia('gallery') as $media)
                                            <div class="relative group">
                                                <img src="{{ $media->getUrl('thumb') }}" class="w-16 h-16 object-cover rounded-lg border border-zinc-200 dark:border-zinc-700" />
                                                <button type="button"
                                                    wire:click="removeMedia({{ $media->id }})"
                                                    class="absolute -top-1.5 -right-1.5 bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs opacity-0 group-hover:opacity-100 transition-opacity">
                                                    ×
                                                </button>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            {{-- Documents --}}
                            @if($find->hasMedia('document'))
                                <div>
                                    <flux:text class="font-medium mb-2">{{ __('Mevcut Belgeler') }}</flux:text>
                                    <div class="space-y-1">
                                        @foreach($find->getMedia('document') as $media)
                                            <div class="flex items-center justify-between py-1 px-3 bg-zinc-50 dark:bg-zinc-800 rounded-lg">
                                                <div class="flex items-center gap-2 text-sm">
                                                    <flux:icon icon="document" class="size-4 text-zinc-400" />
                                                    <span>{{ $media->file_name }}</span>
                                                    <span class="text-zinc-400">{{ number_format($media->size / 1024, 0) }} KB</span>
                                                </div>
                                                <flux:button size="sm" variant="ghost" icon="trash" class="text-red-400"
                                                    wire:click="removeMedia({{ $media->id }})" />
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        @endisset

                        {{-- Yeni Ana Görsel --}}
                        @if(!$activeProject || $activeProject->isFieldVisible('cover_photo'))
                            <div>
                                <flux:input
                                    wire:model="@isset($find) newCoverPhoto @else coverPhoto @endisset"
                                    type="file"
                                    label="{{ isset($find) && $find->hasMedia('cover') ? __('Ana Görseli Değiştir') : __('Ana Görsel (Kapak)') }}"
                                    accept="image/*"
                                    description="{{ __('JPG, PNG, WebP — maks. 10MB') }}"
                                />
                                @isset($find)
                                    @if($newCoverPhoto)
                                        <div class="mt-2">
                                            <img src="{{ $newCoverPhoto->temporaryUrl() }}" class="w-24 h-24 object-cover rounded-lg" />
                                        </div>
                                    @endif
                                @else
                                    @if($coverPhoto)
                                        <div class="mt-2">
                                            <img src="{{ $coverPhoto->temporaryUrl() }}" class="w-24 h-24 object-cover rounded-lg" />
                                        </div>
                                    @endif
                                @endisset
                            </div>
                        @endif

                        {{-- Galeri --}}
                        @if(!$activeProject || $activeProject->isFieldVisible('gallery'))
                            <div>
                                <flux:input
                                    wire:model="@isset($find) newGallery @else gallery @endisset"
                                    type="file"
                                    multiple
                                    label="{{ __('Galeri Fotoğrafları') }}"
                                    accept="image/*"
                                    description="{{ __('Birden fazla seçilebilir — maks. 10MB/dosya') }}"
                                />
                            </div>
                        @endif

                        {{-- Belgeler --}}
                        @if(!$activeProject || $activeProject->isFieldVisible('documents'))
                            <div>
                                <flux:input
                                    wire:model="@isset($find) newDocuments @else documents @endisset"
                                    type="file"
                                    multiple
                                    label="{{ __('Belgeler & Çizimler') }}"
                                    accept=".pdf,.svg,.png,.jpg,.jpeg"
                                    description="{{ __('PDF, SVG, PNG — maks. 20MB/dosya') }}"
                                />
                            </div>
                        @endif
                    </flux:card>
                </div>
            </div>
        @endif

        {{-- Kaydet Butonu --}}
        <div class="flex items-center justify-between pt-2">
            <a href="{{ ($project && $project->exists) ? route('finds.index', $project) : route('all-finds.index') }}" wire:navigate>
                <flux:button variant="ghost" icon="arrow-left">{{ __('Geri Dön') }}</flux:button>
            </a>
            <flux:button type="submit" variant="primary" icon="check">
                {{ isset($find) ? __('Güncelle') : __('Kaydet') }}
            </flux:button>
        </div>

    </form>
</div>
