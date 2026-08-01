<div class="space-y-6">
    {{-- Başlık + Breadcrumb --}}
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2 text-sm text-zinc-400 mb-1">
                <a href="{{ route('excavation-projects.index') }}" wire:navigate class="hover:text-zinc-600 dark:hover:text-zinc-200">{{ __('Kazı Projeleri') }}</a>
                @if($project && $project->exists)
                    <flux:icon icon="chevron-right" class="size-4" />
                    <a href="{{ route('finds.index', $project) }}" wire:navigate class="hover:text-zinc-600 dark:hover:text-zinc-200">{{ $project->name }}</a>
                @endif
                @if($find && $find->exists)
                    <flux:icon icon="chevron-right" class="size-4" />
                    <span class="text-zinc-600 dark:text-zinc-300 font-mono font-medium">{{ $find->inventory_number }}</span>
                @endif
            </div>
            <flux:heading size="xl">{{ __('Sikkeler') }}</flux:heading>
        </div>
        @can('coins.create')
            @if($project && $project->exists && $find && $find->exists)
                <flux:button icon="plus" variant="primary" :href="route('coins.create', [$project, $find])" wire:navigate>
                    {{ __('Yeni Sikke') }}
                </flux:button>
            @else
                <flux:button icon="plus" variant="primary" :href="route('all-coins.create')" wire:navigate>
                    {{ __('Yeni Sikke') }}
                </flux:button>
            @endif
        @endcan
    </div>

    {{-- Filtreler + Görünüm --}}
    <div class="flex flex-col sm:flex-row gap-3">
        <div class="flex-1">
            <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass"
                placeholder="{{ __('Lejand, referans, not...') }}" clearable />
        </div>
        <flux:select wire:model.live="filterPeriod" class="sm:w-40">
            <flux:select.option value="">{{ __('Tüm Dönemler') }}</flux:select.option>
            @foreach($this->periods as $p)
                <flux:select.option value="{{ $p->id }}">{{ $p->getTranslation('name', app()->getLocale(), false) }}</flux:select.option>
            @endforeach
        </flux:select>
        <flux:select wire:model.live="filterMetal" class="sm:w-32">
            <flux:select.option value="">{{ __('Tüm Metaller') }}</flux:select.option>
            @foreach($this->metals as $m)
                <flux:select.option value="{{ $m->id }}">{{ $m->code ?? $m->getTranslation('name', app()->getLocale(), false) }}</flux:select.option>
            @endforeach
        </flux:select>
        {{-- Grid / Tablo geçiş --}}
        <div class="flex rounded-lg border border-zinc-200 dark:border-zinc-700 overflow-hidden">
            <button type="button" wire:click="setViewMode('grid')"
                @class(['px-3 py-2 text-sm transition-colors', 'bg-brand text-white' => $viewMode === 'grid', 'bg-white dark:bg-zinc-900 text-zinc-500 hover:bg-zinc-50' => $viewMode !== 'grid'])>
                <flux:icon icon="squares-2x2" class="size-4" />
            </button>
            <button type="button" wire:click="setViewMode('table')"
                @class(['px-3 py-2 text-sm transition-colors', 'bg-brand text-white' => $viewMode === 'table', 'bg-white dark:bg-zinc-900 text-zinc-500 hover:bg-zinc-50' => $viewMode !== 'table'])>
                <flux:icon icon="list-bullet" class="size-4" />
            </button>
        </div>
    </div>

    {{-- Boş Durum --}}
    @if($this->coins->isEmpty())
        <div class="py-16 text-center">
            <flux:icon icon="circle-stack" class="mx-auto size-12 text-zinc-300 dark:text-zinc-600" />
            <flux:heading class="mt-4 text-zinc-400">{{ __('Henüz sikke yok') }}</flux:heading>
            <flux:text class="mt-1 text-zinc-400 text-sm">
                {{ $search || $filterPeriod || $filterMetal ? __('Filtreye uyan sikke bulunamadı.') : __('"Yeni Sikke" butonuna tıklayarak başlayın.') }}
            </flux:text>
        </div>

    {{-- GRID GÖRÜNÜM --}}
    @elseif($viewMode === 'grid')
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-4">
            @foreach($this->coins as $coin)
                <div wire:key="coin-{{ $coin->id }}"
                     class="group bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-700 shadow-sm hover:shadow-md transition-all overflow-hidden">

                    {{-- Ön Yüz Thumbnail --}}
                    <a href="{{ route('coins.show', [$coin->project ?? $project, $coin->find ?? $find, $coin]) }}" wire:navigate>
                        @if($coin->hasMedia('obverse'))
                            <img src="{{ $coin->getFirstMediaUrl('obverse', 'thumb') }}"
                                 class="w-full aspect-square object-cover bg-zinc-100 dark:bg-zinc-800" />
                        @else
                            <div class="w-full aspect-square bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center">
                                <flux:icon icon="circle-stack" class="size-8 text-zinc-300 dark:text-zinc-600" />
                            </div>
                        @endif
                    </a>

                    <div class="p-2 space-y-1">
                        {{-- Metal badge --}}
                        @if($coin->metal)
                            <flux:badge color="amber" size="sm">{{ $coin->metal->code ?? $coin->metal->getTranslation('name', app()->getLocale(), false) }}</flux:badge>
                        @endif
                        {{-- Dönem --}}
                        @if($coin->period)
                            <p class="text-xs text-zinc-500 truncate">{{ $coin->period->getTranslation('name', app()->getLocale(), false) }}</p>
                        @endif
                        {{-- Ölçüler --}}
                        @if($coin->weight || $coin->diameter)
                            <p class="text-xs text-zinc-400">
                                {{ $coin->weight ? $coin->weight . ' g' : '' }}
                                {{ $coin->weight && $coin->diameter ? '·' : '' }}
                                {{ $coin->diameter ? $coin->diameter . ' mm' : '' }}
                            </p>
                        @endif
                        {{-- Aksiyon butonları --}}
                        <div class="flex gap-1 pt-1">
                            @can('coins.update')
                                <flux:button size="sm" variant="ghost" icon="pencil" class="flex-1"
                                    :href="route('coins.edit', [$coin->project ?? $project, $coin->find ?? $find, $coin])" wire:navigate />
                            @endcan
                            @can('coins.delete')
                                <flux:button size="sm" variant="ghost" icon="trash" class="text-red-400"
                                    wire:click="confirmDelete({{ $coin->id }})" />
                            @endcan
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

    {{-- TABLO GÖRÜNÜM --}}
    @else
        <div class="rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 overflow-hidden shadow-xs">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-zinc-600 dark:text-zinc-400">
                    <thead class="bg-zinc-50 dark:bg-zinc-800/60 text-xs font-bold uppercase text-zinc-500 border-b border-zinc-200 dark:border-zinc-800">
                        <tr>
                            <th class="px-6 py-4 w-12"></th>
                            <th class="px-6 py-4">{{ __('Dönem') }}</th>
                            <th class="px-6 py-4">{{ __('Metal') }}</th>
                            <th class="px-6 py-4">{{ __('Darphane') }}</th>
                            <th class="px-6 py-4">{{ __('Çap/Ağırlık') }}</th>
                            <th class="px-6 py-4">{{ __('Ön Yüz Lejandı') }}</th>
                            <th class="px-6 py-4 text-right"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                        @foreach($this->coins as $coin)
                            <tr wire:key="coin-row-{{ $coin->id }}" class="hover:bg-zinc-50/60 dark:hover:bg-zinc-800/40 transition-colors">
                                <td class="px-6 py-4">
                                    @if($coin->hasMedia('obverse'))
                                        <a href="{{ route('coins.show', [$coin->project ?? $project, $coin->find ?? $find, $coin]) }}" wire:navigate>
                                            <img src="{{ $coin->getFirstMediaUrl('obverse', 'thumb') }}" class="w-10 h-10 object-cover rounded-lg border border-zinc-200 dark:border-zinc-700" />
                                        </a>
                                    @else
                                        <div class="w-10 h-10 rounded-lg bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center">
                                            <flux:icon icon="circle-stack" class="size-5 text-zinc-300" />
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm font-medium text-zinc-900 dark:text-white">{{ $coin->period?->getTranslation('name', app()->getLocale(), false) ?? '—' }}</td>
                                <td class="px-6 py-4">
                                    @if($coin->metal)
                                        <flux:badge color="amber" size="sm">{{ $coin->metal->code ?? $coin->metal->getTranslation('name', app()->getLocale(), false) }}</flux:badge>
                                    @else
                                        <span class="text-zinc-300">—</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm text-zinc-500">{{ $coin->mint?->getTranslation('name', app()->getLocale(), false) ?? '—' }}</td>
                                <td class="px-6 py-4 text-sm text-zinc-400">
                                    {{ $coin->diameter ? $coin->diameter . ' mm' : '' }}
                                    {{ $coin->weight ? ' / ' . $coin->weight . ' g' : '' }}
                                    {{ (!$coin->diameter && !$coin->weight) ? '—' : '' }}
                                </td>
                                <td class="px-6 py-4 text-sm font-mono text-zinc-500 max-w-[180px] truncate">
                                    {{ $coin->obverse_legend ?? '—' }}
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex justify-end gap-1">
                                        @can('coins.view')
                                            <flux:button size="sm" variant="ghost" icon="eye"
                                                :href="route('coins.show', [$coin->project ?? $project, $coin->find ?? $find, $coin])" wire:navigate />
                                        @endcan
                                        @can('coins.update')
                                            <flux:button size="sm" variant="ghost" icon="pencil"
                                                :href="route('coins.edit', [$coin->project ?? $project, $coin->find ?? $find, $coin])" wire:navigate />
                                        @endcan
                                        @can('coins.delete')
                                            <flux:button size="sm" variant="ghost" icon="trash" class="text-red-400"
                                                wire:click="confirmDelete({{ $coin->id }})" />
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- Sayfalama --}}
    @if($this->coins->total() > 0)
        <div>{{ $this->coins->links() }}</div>
    @endif

{{-- Silme Onay --}}
<flux:modal wire:model="showDeleteModal" class="max-w-sm">
    <div class="space-y-4">
        <flux:heading size="lg">{{ __('Sikkeyi Sil') }}</flux:heading>
        <flux:text class="text-zinc-500">{{ __('Bu sikkeyi silmek istediğinizden emin misiniz? Tüm görseller de silinecek.') }}</flux:text>
        <div class="flex justify-end gap-3">
            <flux:button variant="ghost" wire:click="$set('showDeleteModal', false)">{{ __('Vazgeç') }}</flux:button>
            <flux:button variant="danger" wire:click="delete">{{ __('Sil') }}</flux:button>
        </div>
    </div>
</flux:modal>
</div>
