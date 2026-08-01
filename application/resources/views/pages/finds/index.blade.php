<div class="space-y-6">
    {{-- Başlık + Breadcrumb + Yeni Ekle --}}
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2 text-sm text-zinc-400 mb-1">
                <a href="{{ route('excavation-projects.index') }}" wire:navigate class="hover:text-zinc-600 dark:hover:text-zinc-200">{{ __('Kazı Projeleri') }}</a>
                @if($project && $project->exists)
                    <flux:icon icon="chevron-right" class="size-4" />
                    <span class="text-zinc-600 dark:text-zinc-300 font-medium">{{ $project->name }}</span>
                @endif
            </div>
            <flux:heading size="xl">{{ __('Buluntular') }}</flux:heading>
            @if($project && $project->exists)
                <flux:text class="mt-0.5 text-zinc-500">{{ $project->site_name }}</flux:text>
            @endif
        </div>
        @can('finds.create')
            @if($project && $project->exists)
                <flux:button icon="plus" variant="primary" :href="route('finds.create', $project)" wire:navigate>
                    {{ __('Yeni Buluntu') }}
                </flux:button>
            @else
                <flux:button icon="plus" variant="primary" :href="route('all-finds.create')" wire:navigate>
                    {{ __('Yeni Buluntu') }}
                </flux:button>
            @endif
        @endcan
    </div>

    {{-- Filtreler --}}
    <div class="flex flex-col sm:flex-row gap-3">
        <div class="flex-1">
            <flux:input
                wire:model.live.debounce.300ms="search"
                icon="magnifying-glass"
                placeholder="{{ __('Envanter no, alan...') }}"
                clearable
            />
        </div>
        @if(count($this->seasons) > 0)
            <flux:select wire:model.live="filterSeason" class="sm:w-44">
                <flux:select.option value="">{{ __('Tüm Sezonlar') }}</flux:select.option>
                @foreach($this->seasons as $season)
                    <flux:select.option value="{{ $season }}">{{ $season }}</flux:select.option>
                @endforeach
            </flux:select>
        @endif
    </div>

    {{-- Tablo --}}
    @if($this->finds->isEmpty())
        <div class="py-16 text-center">
            <flux:icon icon="archive-box" class="mx-auto size-12 text-zinc-300 dark:text-zinc-600" />
            <flux:heading class="mt-4 text-zinc-400">{{ __('Henüz buluntu yok') }}</flux:heading>
            <flux:text class="mt-1 text-zinc-400 text-sm">
                {{ $search ? __('Arama kriterine uyan buluntu bulunamadı.') : __('"Yeni Buluntu" butonuna tıklayarak başlayın.') }}
            </flux:text>
        </div>
    @else
        <div class="rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 overflow-hidden shadow-xs">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-zinc-600 dark:text-zinc-400">
                    <thead class="bg-zinc-50 dark:bg-zinc-800/60 text-xs font-bold uppercase text-zinc-500 border-b border-zinc-200 dark:border-zinc-800">
                        <tr>
                            <th class="px-6 py-4 w-12"></th>
                            <th class="px-6 py-4">{{ __('Envanter No') }}</th>
                            <th class="px-6 py-4">{{ __('Tarih') }}</th>
                            <th class="px-6 py-4">{{ __('Kazı Alanı') }}</th>
                            <th class="px-6 py-4">{{ __('Bağlam') }}</th>
                            <th class="px-6 py-4 text-center">{{ __('Sikke') }}</th>
                            <th class="px-6 py-4 text-right"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                        @foreach($this->finds as $find)
                            <tr wire:key="find-{{ $find->id }}" class="hover:bg-zinc-50/60 dark:hover:bg-zinc-800/40 transition-colors">
                                <td class="px-6 py-4">
                                    @if($find->hasMedia('cover'))
                                        <img src="{{ $find->getFirstMediaUrl('cover', 'thumb') }}"
                                             class="w-10 h-10 object-cover rounded-lg border border-zinc-200 dark:border-zinc-700" />
                                    @else
                                        <div class="w-10 h-10 rounded-lg bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center">
                                            <flux:icon icon="photo" class="size-5 text-zinc-300 dark:text-zinc-600" />
                                        </div>
                                    @endif
                                </td>

                                <td class="px-6 py-4 font-mono font-medium text-amber-600 dark:text-amber-400">
                                    {{ $find->inventory_number }}
                                </td>

                                <td class="px-6 py-4 text-sm text-zinc-500">
                                    {{ $find->find_date?->format('d.m.Y') }}
                                </td>

                                <td class="px-6 py-4 text-zinc-900 dark:text-white">
                                    {{ $find->excavation_area }}
                                    @if($find->excavation_season)
                                        <span class="ml-1 text-xs text-zinc-400">({{ $find->excavation_season }})</span>
                                    @endif
                                </td>

                                <td class="px-6 py-4 text-sm text-zinc-400">
                                    {{ collect([$find->sector, $find->trench, $find->locus, $find->context])->filter()->take(2)->implode(' / ') ?: '—' }}
                                </td>

                                <td class="px-6 py-4 text-center">
                                    <a href="{{ route('coins.index', [$find->project ?? $project, $find]) }}" wire:navigate
                                       class="inline-flex items-center gap-1 text-sm font-medium text-zinc-600 hover:text-amber-600 dark:hover:text-amber-400 transition-colors">
                                        <flux:icon icon="circle-stack" class="size-4" />
                                        {{ $find->coins_count }}
                                    </a>
                                </td>

                                <td class="px-6 py-4 text-right">
                                    <div class="flex justify-end gap-1">
                                        @can('finds.create')
                                            <flux:button size="sm" variant="ghost" icon="circle-stack"
                                                :href="route('coins.create', [$find->project ?? $project, $find])" wire:navigate
                                                x-tooltip="{{ __('Sikke Ekle') }}" />
                                        @endcan
                                        @can('finds.update')
                                            <flux:button size="sm" variant="ghost" icon="pencil"
                                                :href="route('finds.edit', [$find->project ?? $project, $find])" wire:navigate
                                                x-tooltip="{{ __('Düzenle') }}" />
                                        @endcan
                                        @can('finds.delete')
                                            <flux:button size="sm" variant="ghost" icon="trash"
                                                class="text-red-400 hover:text-red-600"
                                                wire:click="confirmDelete({{ $find->id }})"
                                                x-tooltip="{{ __('Sil') }}" />
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Sayfalama --}}
        <div>{{ $this->finds->links() }}</div>
    @endif

{{-- Silme Onay --}}
<flux:modal wire:model="showDeleteModal" class="max-w-sm">
    <div class="space-y-4">
        <flux:heading size="lg">{{ __('Buluntuyu Sil') }}</flux:heading>
        <flux:text class="text-zinc-500">
            {{ __('Bu buluntuyu silmek istediğinizden emin misiniz? Bağlı sikke varsa silinemez.') }}
        </flux:text>
        <div class="flex justify-end gap-3">
            <flux:button variant="ghost" wire:click="$set('showDeleteModal', false)">{{ __('Vazgeç') }}</flux:button>
            <flux:button variant="danger" wire:click="delete">{{ __('Sil') }}</flux:button>
        </div>
    </div>
</flux:modal>
</div>
