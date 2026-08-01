<?php

use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Livewire\Component;
use App\Models\ExcavationProject;
use App\Models\Find;
use App\Models\Coin;
use App\Models\Dictionary;

new #[Title('Dashboard')] #[Layout('layouts.app')] class extends Component {
    public function with(): array
    {
        $accessibleProjectIds = ExcavationProject::accessibleBy()->pluck('id');

        $findIds = Find::whereIn('excavation_project_id', $accessibleProjectIds)->pluck('id');
        $coinIds = Coin::whereIn('excavation_project_id', $accessibleProjectIds)->pluck('id');

        $totalMedia = \Spatie\MediaLibrary\MediaCollections\Models\Media::where(function ($q) use ($findIds, $coinIds) {
            $q->where(fn ($mq) => $mq->where('model_type', Find::class)->whereIn('model_id', $findIds))
              ->orWhere(fn ($mq) => $mq->where('model_type', Coin::class)->whereIn('model_id', $coinIds));
        })->count();

        return [
            'totalProjects'  => ExcavationProject::accessibleBy()->count(),
            'activeProjects' => ExcavationProject::accessibleBy()->where('is_active', true)->count(),
            'totalFinds'     => Find::whereIn('excavation_project_id', $accessibleProjectIds)->count(),
            'totalCoins'     => Coin::whereIn('excavation_project_id', $accessibleProjectIds)->count(),
            'totalMedia'     => $totalMedia,
            'recentFinds'    => Find::whereIn('excavation_project_id', $accessibleProjectIds)->with('project')->latest()->take(5)->get(),
            'recentCoins'    => Coin::whereIn('excavation_project_id', $accessibleProjectIds)->with(['project', 'find', 'period', 'metal'])->latest()->take(5)->get(),
            'metalStats'     => Dictionary::ofType('metal')->withCount(['coinsAsMetal' => function ($q) use ($accessibleProjectIds) {
                $q->whereIn('excavation_project_id', $accessibleProjectIds);
            }])->get(),
        ];
    }
};
?>

<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <flux:heading size="xl">{{ __('Genel Bakış') }}</flux:heading>
            <flux:text class="mt-1 text-zinc-500">{{ __('ExcaCoin Arkeolojik Kazı ve Nümismatik Yönetim Sistemi') }}</flux:text>
        </div>
        <div class="flex gap-2">
            @can('excavation_projects.create')
                <flux:button icon="plus" variant="primary" :href="route('excavation-projects.index')" wire:navigate>
                    {{ __('Projeleri Yönet') }}
                </flux:button>
            @endcan
        </div>
    </div>

    {{-- Stat Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Projeler --}}
        <flux:card class="p-5 flex items-center gap-4">
            <div class="size-12 rounded-xl bg-amber-500/15 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0">
                <flux:icon icon="map-pin" class="size-6" />
            </div>
            <div>
                <flux:text class="text-xs text-zinc-400 font-medium uppercase tracking-wider">{{ __('Kazı Projeleri') }}</flux:text>
                <div class="flex items-baseline gap-2 mt-1">
                    <span class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">{{ $totalProjects }}</span>
                    <span class="text-xs text-emerald-600 font-medium">({{ $activeProjects }} {{ __('aktif') }})</span>
                </div>
            </div>
        </flux:card>

        {{-- Buluntular --}}
        <flux:card class="p-5 flex items-center gap-4">
            <div class="size-12 rounded-xl bg-blue-500/15 text-blue-600 dark:text-blue-400 flex items-center justify-center shrink-0">
                <flux:icon icon="archive-box" class="size-6" />
            </div>
            <div>
                <flux:text class="text-xs text-zinc-400 font-medium uppercase tracking-wider">{{ __('Kayıtlı Buluntular') }}</flux:text>
                <div class="mt-1">
                    <span class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">{{ $totalFinds }}</span>
                </div>
            </div>
        </flux:card>

        {{-- Sikkeler --}}
        <flux:card class="p-5 flex items-center gap-4">
            <div class="size-12 rounded-xl bg-amber-600/15 text-amber-700 dark:text-amber-300 flex items-center justify-center shrink-0">
                <flux:icon icon="circle-stack" class="size-6" />
            </div>
            <div>
                <flux:text class="text-xs text-zinc-400 font-medium uppercase tracking-wider">{{ __('Toplam Sikke') }}</flux:text>
                <div class="mt-1">
                    <span class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">{{ $totalCoins }}</span>
                </div>
            </div>
        </flux:card>

        {{-- Medya Kütüphanesi --}}
        <flux:card class="p-5 flex items-center gap-4">
            <div class="size-12 rounded-xl bg-purple-500/15 text-purple-600 dark:text-purple-400 flex items-center justify-center shrink-0">
                <flux:icon icon="photo" class="size-6" />
            </div>
            <div>
                <flux:text class="text-xs text-zinc-400 font-medium uppercase tracking-wider">{{ __('Görseller & Medya') }}</flux:text>
                <div class="mt-1">
                    <span class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">{{ $totalMedia }}</span>
                </div>
            </div>
        </flux:card>
    </div>

    {{-- Alt Izgara: Son Eklenenler ve Metal Dağılımı --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Son Eklenen Buluntular & Sikkeler (2 Sütun) --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Son Eklenen Sikkeler --}}
            <flux:card class="p-5">
                <div class="flex items-center justify-between mb-4">
                    <flux:heading size="md" class="flex items-center gap-2">
                        <flux:icon icon="circle-stack" class="size-4 text-amber-500" />
                        {{ __('Son Eklenen Sikkeler') }}
                    </flux:heading>
                </div>

                @if($recentCoins->isEmpty())
                    <p class="text-sm text-zinc-400 py-4 text-center">{{ __('Henüz kayıtlı sikke bulunmuyor.') }}</p>
                @else
                    <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        @foreach($recentCoins as $coin)
                            <div class="py-3 flex items-center justify-between gap-4">
                                <div class="flex items-center gap-3 min-w-0">
                                    @if($coin->hasMedia('obverse'))
                                        <img src="{{ $coin->getFirstMediaUrl('obverse', 'thumb') }}" class="size-10 rounded-lg object-cover border shrink-0" />
                                    @else
                                        <div class="size-10 rounded-lg bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center shrink-0">
                                            <flux:icon icon="circle-stack" class="size-5 text-zinc-400" />
                                        </div>
                                    @endif
                                    <div class="min-w-0">
                                        <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100 truncate">
                                            {{ $coin->period?->getTranslation('name', app()->getLocale(), false) ?? __('Dönem Belirtilmemiş') }}
                                        </p>
                                        <p class="text-xs text-zinc-400 truncate">
                                            {{ $coin->project->name ?? '' }} · {{ $coin->find->inventory_number ?? '' }}
                                        </p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2 shrink-0">
                                    @if($coin->metal)
                                        <flux:badge color="amber" size="sm">{{ $coin->metal->code ?? $coin->metal->getTranslation('name', app()->getLocale(), false) }}</flux:badge>
                                    @endif
                                    <flux:button size="sm" variant="ghost" icon="arrow-right" :href="route('coins.show', [$coin->project, $coin->find, $coin])" wire:navigate />
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </flux:card>

            {{-- Son Eklenen Buluntular --}}
            <flux:card class="p-5">
                <div class="flex items-center justify-between mb-4">
                    <flux:heading size="md" class="flex items-center gap-2">
                        <flux:icon icon="archive-box" class="size-4 text-blue-500" />
                        {{ __('Son Eklenen Buluntular') }}
                    </flux:heading>
                </div>

                @if($recentFinds->isEmpty())
                    <p class="text-sm text-zinc-400 py-4 text-center">{{ __('Henüz kayıtlı buluntu bulunmuyor.') }}</p>
                @else
                    <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        @foreach($recentFinds as $find)
                            <div class="py-3 flex items-center justify-between gap-4">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="size-10 rounded-lg bg-blue-50 dark:bg-blue-950/40 text-blue-600 flex items-center justify-center shrink-0 font-mono text-xs font-bold">
                                        FIND
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100 font-mono truncate">
                                            {{ $find->inventory_number }}
                                        </p>
                                        <p class="text-xs text-zinc-400 truncate">
                                            {{ $find->project->name ?? '' }} · {{ $find->excavation_area }}
                                        </p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2 shrink-0">
                                    <span class="text-xs text-zinc-400">{{ $find->find_date?->format('d.m.Y') }}</span>
                                    <flux:button size="sm" variant="ghost" icon="arrow-right" :href="route('finds.index', $find->project)" wire:navigate />
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </flux:card>

        </div>

        {{-- Metal Dağılımı ve Hızlı Bağlantılar --}}
        <div class="space-y-6">
            <flux:card class="p-5">
                <flux:heading size="md" class="mb-4">{{ __('Metal Dağılımı') }}</flux:heading>
                <div class="space-y-3">
                    @foreach($metalStats as $metal)
                        <div>
                            <div class="flex justify-between text-xs font-medium mb-1">
                                <span class="text-zinc-700 dark:text-zinc-300">
                                    {{ $metal->getTranslation('name', app()->getLocale(), false) }}
                                    @if($metal->code) <span class="text-zinc-400">({{ $metal->code }})</span> @endif
                                </span>
                                <span class="text-zinc-500">{{ $metal->coins_as_metal_count }} {{ __('adet') }}</span>
                            </div>
                            <div class="w-full bg-zinc-100 dark:bg-zinc-800 rounded-full h-2 overflow-hidden">
                                <div class="bg-amber-500 h-2 rounded-full" style="width: {{ $totalCoins > 0 ? min(100, round(($metal->coins_as_metal_count / $totalCoins) * 100)) : 0 }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </flux:card>

            <flux:card class="p-5 space-y-3">
                <flux:heading size="md">{{ __('Hızlı Erişim') }}</flux:heading>
                <div class="space-y-2">
                    <a href="{{ route('excavation-projects.index') }}" wire:navigate class="flex items-center justify-between p-2.5 rounded-lg bg-zinc-50 dark:bg-zinc-800/60 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors text-sm font-medium">
                        <span class="flex items-center gap-2">
                            <flux:icon icon="map-pin" class="size-4 text-amber-500" />
                            {{ __('Kazı Projeleri') }}
                        </span>
                        <flux:icon icon="chevron-right" class="size-4 text-zinc-400" />
                    </a>
                    <a href="{{ route('dictionaries.index') }}" wire:navigate class="flex items-center justify-between p-2.5 rounded-lg bg-zinc-50 dark:bg-zinc-800/60 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors text-sm font-medium">
                        <span class="flex items-center gap-2">
                            <flux:icon icon="book-open" class="size-4 text-amber-500" />
                            {{ __('Nümismatik Sözlükler') }}
                        </span>
                        <flux:icon icon="chevron-right" class="size-4 text-zinc-400" />
                    </a>
                </div>
            </flux:card>
        </div>

    </div>
</div>
