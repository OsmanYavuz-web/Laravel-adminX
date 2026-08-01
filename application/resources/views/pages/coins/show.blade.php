<div class="max-w-5xl mx-auto space-y-6">

    {{-- Breadcrumb + Aksiyonlar --}}
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-2 text-sm text-zinc-400">
            <a href="{{ route('excavation-projects.index') }}" wire:navigate class="hover:text-zinc-600 dark:hover:text-zinc-200">{{ __('Kazı Projeleri') }}</a>
            <flux:icon icon="chevron-right" class="size-4" />
            <a href="{{ route('finds.index', $project) }}" wire:navigate class="hover:text-zinc-600 dark:hover:text-zinc-200">{{ $project->name }}</a>
            <flux:icon icon="chevron-right" class="size-4" />
            <a href="{{ route('coins.index', [$project, $find]) }}" wire:navigate class="hover:text-zinc-600 dark:hover:text-zinc-200 font-mono">{{ $find->inventory_number }}</a>
            <flux:icon icon="chevron-right" class="size-4" />
            <span class="text-zinc-600 dark:text-zinc-300">{{ __('Sikke') }} #{{ $coin->id }}</span>
        </div>
        @can('coins.update')
            <flux:button icon="pencil" variant="primary" :href="route('coins.edit', [$project, $find, $coin])" wire:navigate>
                {{ __('Düzenle') }}
            </flux:button>
        @endcan
    </div>

    {{-- Ana İçerik --}}
    <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">

        {{-- Sol: Ön/Arka Yüz Görselleri --}}
        <div class="lg:col-span-2 space-y-4">
            {{-- Ön Yüz --}}
            <flux:card class="text-center p-4">
                <flux:text class="font-medium text-zinc-500 mb-3">{{ __('Ön Yüz') }}</flux:text>
                @if($coin->hasMedia('obverse'))
                    <img src="{{ $coin->getFirstMediaUrl('obverse', 'preview') }}"
                         class="mx-auto max-h-56 object-contain rounded-lg" />
                @else
                    <div class="mx-auto w-40 h-40 rounded-full bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center">
                        <flux:icon icon="circle-stack" class="size-12 text-zinc-300" />
                    </div>
                @endif
            </flux:card>

            {{-- Arka Yüz --}}
            <flux:card class="text-center p-4">
                <flux:text class="font-medium text-zinc-500 mb-3">{{ __('Arka Yüz') }}</flux:text>
                @if($coin->hasMedia('reverse'))
                    <img src="{{ $coin->getFirstMediaUrl('reverse', 'preview') }}"
                         class="mx-auto max-h-56 object-contain rounded-lg" />
                @else
                    <div class="mx-auto w-40 h-40 rounded-full bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center">
                        <flux:icon icon="circle-stack" class="size-12 text-zinc-300 rotate-180" />
                    </div>
                @endif
            </flux:card>

            {{-- Galeri --}}
            @if($coin->hasMedia('gallery'))
                <flux:card class="p-3">
                    <flux:text class="font-medium text-zinc-500 mb-2 text-sm">{{ __('Galeri') }}</flux:text>
                    <div class="flex flex-wrap gap-2">
                        @foreach($coin->getMedia('gallery') as $media)
                            <a href="{{ $media->getUrl() }}" target="_blank">
                                <img src="{{ $media->getUrl('thumb') }}" class="w-14 h-14 object-cover rounded-lg border border-zinc-200 dark:border-zinc-700 hover:opacity-90 transition-opacity" />
                            </a>
                        @endforeach
                    </div>
                </flux:card>
            @endif

            {{-- Belgeler --}}
            @if($coin->hasMedia('document'))
                <flux:card class="p-3 space-y-1">
                    <flux:text class="font-medium text-zinc-500 mb-2 text-sm">{{ __('Belgeler') }}</flux:text>
                    @foreach($coin->getMedia('document') as $media)
                        <a href="{{ $media->getUrl() }}" target="_blank"
                           class="flex items-center gap-2 text-sm text-zinc-600 hover:text-amber-600 dark:text-zinc-400 dark:hover:text-amber-400 transition-colors">
                            <flux:icon icon="document" class="size-4 shrink-0" />
                            <span class="truncate">{{ $media->file_name }}</span>
                        </a>
                    @endforeach
                </flux:card>
            @endif
        </div>

        {{-- Sağ: Detaylar --}}
        <div class="lg:col-span-3 space-y-4">

            {{-- Fiziksel Özellikler --}}
            <flux:card class="p-5">
                <flux:heading size="md" class="mb-4">{{ __('Fiziksel Özellikler') }}</flux:heading>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                    @if($coin->metal)
                        <div>
                            <flux:text class="text-xs text-zinc-400 uppercase tracking-wide">{{ __('Metal') }}</flux:text>
                            <flux:badge color="amber" class="mt-1">
                                {{ $coin->metal->code ? $coin->metal->code . ' — ' : '' }}{{ $coin->metal->getTranslation('name', app()->getLocale(), false) }}
                            </flux:badge>
                        </div>
                    @endif
                    @if($coin->diameter)
                        <div>
                            <flux:text class="text-xs text-zinc-400 uppercase tracking-wide">{{ __('Çap') }}</flux:text>
                            <p class="font-semibold text-zinc-800 dark:text-zinc-200 mt-1">{{ $coin->diameter }} mm</p>
                        </div>
                    @endif
                    @if($coin->weight)
                        <div>
                            <flux:text class="text-xs text-zinc-400 uppercase tracking-wide">{{ __('Ağırlık') }}</flux:text>
                            <p class="font-semibold text-zinc-800 dark:text-zinc-200 mt-1">{{ $coin->weight }} g</p>
                        </div>
                    @endif
                    @if($coin->axis)
                        <div>
                            <flux:text class="text-xs text-zinc-400 uppercase tracking-wide">{{ __('Kalıp Yönü') }}</flux:text>
                            <p class="font-semibold text-zinc-800 dark:text-zinc-200 mt-1">{{ $coin->axis }}h</p>
                        </div>
                    @endif
                    @if($coin->denomination)
                        <div>
                            <flux:text class="text-xs text-zinc-400 uppercase tracking-wide">{{ __('Birim') }}</flux:text>
                            <p class="font-semibold text-zinc-800 dark:text-zinc-200 mt-1">{{ $coin->denomination->getTranslation('name', app()->getLocale(), false) }}</p>
                        </div>
                    @endif
                    @if($coin->is_cut || $coin->is_pierced || $coin->is_overstrike)
                        <div class="col-span-2 sm:col-span-3 flex flex-wrap gap-2">
                            @if($coin->is_cut)<flux:badge color="red">{{ __('Kesilmiş') }}</flux:badge>@endif
                            @if($coin->is_pierced)<flux:badge color="orange">{{ __('Delinmiş') }}</flux:badge>@endif
                            @if($coin->is_overstrike)<flux:badge color="purple">{{ __('Üst Baskı') }}</flux:badge>@endif
                        </div>
                    @endif
                </div>
            </flux:card>

            {{-- Tanımlama --}}
            <flux:card class="p-5">
                <flux:heading size="md" class="mb-4">{{ __('Tanımlama') }}</flux:heading>
                <dl class="space-y-2 text-sm">
                    @foreach([
                        ['label' => __('Dönem'),    'value' => $coin->period?->getTranslation('name', app()->getLocale(), false)],
                        ['label' => __('Otorite'),  'value' => $coin->authority?->getTranslation('name', app()->getLocale(), false)],
                        ['label' => __('Hükümdar'), 'value' => $coin->ruler?->getTranslation('name', app()->getLocale(), false)],
                        ['label' => __('Bölge'),    'value' => $coin->region?->getTranslation('name', app()->getLocale(), false)],
                        ['label' => __('Darphane'), 'value' => $coin->mint?->getTranslation('name', app()->getLocale(), false)],
                        ['label' => __('Tarih Aralığı'), 'value' => $coin->date_range],
                    ] as $row)
                        @if($row['value'])
                            <div class="flex gap-2">
                                <dt class="w-28 shrink-0 text-zinc-400">{{ $row['label'] }}</dt>
                                <dd class="text-zinc-800 dark:text-zinc-200 font-medium">{{ $row['value'] }}</dd>
                            </div>
                        @endif
                    @endforeach
                </dl>
            </flux:card>

            {{-- Ön Yüz --}}
            @if($coin->obverse_description || $coin->obverse_legend)
                <flux:card class="p-5">
                    <flux:heading size="md" class="mb-3">{{ __('Ön Yüz') }}</flux:heading>
                    <div class="space-y-2 text-sm">
                        @if($coin->obverse_description)
                            <p class="text-zinc-600 dark:text-zinc-400">{{ $coin->obverse_description }}</p>
                        @endif
                        @if($coin->obverse_legend)
                            <div class="font-mono bg-zinc-50 dark:bg-zinc-800 rounded-lg px-3 py-2 text-zinc-700 dark:text-zinc-300">
                                {{ $coin->obverse_legend }}
                                @if($coin->obverse_legend_expanded)
                                    <span class="block text-zinc-400 text-xs mt-1">{{ $coin->obverse_legend_expanded }}</span>
                                @endif
                            </div>
                        @endif
                    </div>
                </flux:card>
            @endif

            {{-- Arka Yüz --}}
            @if($coin->reverse_description || $coin->reverse_legend)
                <flux:card class="p-5">
                    <flux:heading size="md" class="mb-3">{{ __('Arka Yüz') }}</flux:heading>
                    <div class="space-y-2 text-sm">
                        @if($coin->reverse_description)
                            <p class="text-zinc-600 dark:text-zinc-400">{{ $coin->reverse_description }}</p>
                        @endif
                        @if($coin->reverse_legend)
                            <div class="font-mono bg-zinc-50 dark:bg-zinc-800 rounded-lg px-3 py-2 text-zinc-700 dark:text-zinc-300">
                                {{ $coin->reverse_legend }}
                                @if($coin->reverse_legend_expanded)
                                    <span class="block text-zinc-400 text-xs mt-1">{{ $coin->reverse_legend_expanded }}</span>
                                @endif
                            </div>
                        @endif
                    </div>
                </flux:card>
            @endif

            {{-- Referans & Not --}}
            @if($coin->reference || $coin->note)
                <flux:card class="p-5 space-y-3">
                    @if($coin->reference)
                        <div>
                            <flux:text class="text-xs text-zinc-400 uppercase tracking-wide mb-1">{{ __('Referans') }}</flux:text>
                            <p class="text-sm text-zinc-600 dark:text-zinc-400 font-mono">{{ $coin->reference }}</p>
                        </div>
                    @endif
                    @if($coin->note)
                        <div>
                            <flux:text class="text-xs text-zinc-400 uppercase tracking-wide mb-1">{{ __('Açıklama / Kondisyon') }}</flux:text>
                            <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ $coin->note }}</p>
                        </div>
                    @endif
                </flux:card>
            @endif
        </div>
    </div>
</div>
