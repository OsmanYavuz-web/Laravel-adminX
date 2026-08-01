<div class="space-y-6">
    {{-- Başlık --}}
    <div class="flex items-center justify-between">
        <div>
            <flux:heading size="xl">{{ __('Kazı Projeleri') }}</flux:heading>
            <flux:text class="mt-1 text-zinc-500">{{ __('Arkeolojik kazı alanlarını ve projelerini yönetin') }}</flux:text>
        </div>
        @can('excavation_projects.create')
            <flux:button icon="plus" variant="primary" wire:click="create">
                {{ __('Yeni Proje') }}
            </flux:button>
        @endcan
    </div>

    {{-- Filtreler --}}
    <div class="flex flex-col sm:flex-row gap-3">
        <div class="flex-1">
            <flux:input
                wire:model.live.debounce.300ms="search"
                icon="magnifying-glass"
                placeholder="{{ __('Proje adı, alan, konum veya başkan...') }}"
                clearable
            />
        </div>
        <flux:select wire:model.live="filterStatus" class="sm:w-44">
            <flux:select.option value="all">{{ __('Tümü') }}</flux:select.option>
            <flux:select.option value="active">{{ __('Aktif') }}</flux:select.option>
            <flux:select.option value="inactive">{{ __('Pasif') }}</flux:select.option>
        </flux:select>
    </div>

    {{-- Proje Kartları --}}
    @if($this->projects->isEmpty())
        <div class="py-16 text-center">
            <flux:icon icon="map-pin" class="mx-auto size-12 text-zinc-300 dark:text-zinc-600" />
            <flux:heading class="mt-4 text-zinc-400">{{ __('Henüz proje yok') }}</flux:heading>
            <flux:text class="mt-1 text-zinc-400 text-sm">
                {{ $search ? __('Arama kriterlerine uyan proje bulunamadı.') : __('İlk projeyi oluşturmak için "Yeni Proje" butonuna tıklayın.') }}
            </flux:text>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
            @foreach($this->projects as $project)
                <div wire:key="project-{{ $project->id }}"
                     class="group bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-700 shadow-sm hover:shadow-md transition-all duration-200 overflow-hidden flex flex-col">

                    {{-- Kart Başlığı --}}
                    <div class="px-5 pt-5 pb-4 flex-1">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <h3 class="font-semibold text-zinc-900 dark:text-zinc-100 truncate">{{ $project->name }}</h3>
                                <p class="text-sm text-amber-600 dark:text-amber-400 font-medium mt-0.5 truncate">
                                    <flux:icon icon="map-pin" class="inline size-3.5 mr-0.5" />
                                    {{ $project->site_name }}
                                </p>
                            </div>
                            @if($project->is_active)
                                <flux:badge color="green" size="sm" class="shrink-0">{{ __('Aktif') }}</flux:badge>
                            @else
                                <flux:badge color="zinc" size="sm" class="shrink-0">{{ __('Pasif') }}</flux:badge>
                            @endif
                        </div>

                        {{-- Meta bilgiler --}}
                        <div class="mt-3 space-y-1.5 text-sm text-zinc-500 dark:text-zinc-400">
                            @if($project->location || $project->country)
                                <div class="flex items-center gap-1.5">
                                    <flux:icon icon="globe-alt" class="size-3.5 shrink-0" />
                                    <span class="truncate">{{ collect([$project->location, $project->country])->filter()->implode(', ') }}</span>
                                </div>
                            @endif
                            @if($project->start_date)
                                <div class="flex items-center gap-1.5">
                                    <flux:icon icon="calendar" class="size-3.5 shrink-0" />
                                    <span>{{ $project->start_date->format('d.m.Y') }}{{ $project->end_date ? ' – ' . $project->end_date->format('d.m.Y') : ' – ' . __('devam') }}</span>
                                </div>
                            @endif
                            @if($project->director)
                                <div class="flex items-center gap-1.5">
                                    <flux:icon icon="user" class="size-3.5 shrink-0" />
                                    <span class="truncate">{{ $project->director }}</span>
                                </div>
                            @endif
                        </div>

                        @if($project->description)
                            <p class="mt-3 text-xs text-zinc-400 line-clamp-2">{{ $project->description }}</p>
                        @endif
                    </div>

                    {{-- İstatistikler --}}
                    <div class="px-5 py-3 bg-zinc-50 dark:bg-zinc-800/50 border-t border-zinc-100 dark:border-zinc-700/50 flex items-center gap-4">
                        <a href="{{ route('finds.index', $project) }}" wire:navigate
                           class="flex items-center gap-1.5 text-sm text-zinc-500 hover:text-amber-600 dark:hover:text-amber-400 transition-colors">
                            <flux:icon icon="archive-box" class="size-4" />
                            <span class="font-medium">{{ $project->finds_count }}</span>
                            <span>{{ __('buluntu') }}</span>
                        </a>
                        <a href="{{ route('finds.index', $project) }}" wire:navigate
                           class="flex items-center gap-1.5 text-sm text-zinc-500 hover:text-amber-600 dark:hover:text-amber-400 transition-colors">
                            <flux:icon icon="circle-stack" class="size-4" />
                            <span class="font-medium">{{ $project->coins_count }}</span>
                            <span>{{ __('sikke') }}</span>
                        </a>
                        <div class="flex-1"></div>

                        {{-- Aksiyonlar --}}
                        <div class="flex items-center gap-1">
                            @can('excavation_projects.view')
                                <flux:button
                                    size="sm" variant="ghost" icon="arrow-right"
                                    :href="route('finds.index', $project)"
                                    wire:navigate
                                    x-tooltip="{{ __('Buluntuları görüntüle') }}"
                                />
                            @endcan
                            @can('excavation_projects.update')
                                <flux:button
                                    size="sm" variant="ghost" icon="pencil"
                                    wire:click="edit({{ $project->id }})"
                                    x-tooltip="{{ __('Düzenle') }}"
                                />
                            @endcan
                            @can('excavation_projects.delete')
                                <flux:button
                                    size="sm" variant="ghost" icon="trash"
                                    class="text-red-400 hover:text-red-600"
                                    wire:click="confirmDelete({{ $project->id }})"
                                    x-tooltip="{{ __('Sil') }}"
                                />
                            @endcan
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Sayfalama --}}
        <div class="mt-4">
            {{ $this->projects->links() }}
        </div>
    @endif

{{-- Form Modal --}}
<flux:modal wire:model="showProjectModal" class="max-w-2xl">
    <form wire:submit="save" class="space-y-5">
        <div>
            <flux:heading size="lg">
                {{ $editingId ? __('Projeyi Düzenle') : __('Yeni Kazı Projesi') }}
            </flux:heading>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <flux:field>
                <flux:label>
                    {{ __('Proje Adı') }} <span class="ml-1 text-red-500 font-bold" title="{{ __('Zorunlu alan') }}">*</span>
                    <svg class="inline size-3.5 text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200 cursor-help ml-1" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><title>{{ __('Kazı projesinin resmi adı veya unvanı') }}</title><path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" /></svg>
                </flux:label>
                <flux:input
                    wire:model="formName"
                    placeholder="{{ __('Side 2024 Kazısı') }}"
                    required
                />
            </flux:field>
            <flux:field>
                <flux:label>
                    {{ __('Kazı Alanı') }} <span class="ml-1 text-red-500 font-bold" title="{{ __('Zorunlu alan') }}">*</span>
                    <svg class="inline size-3.5 text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200 cursor-help ml-1" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><title>{{ __('Ören yeri veya antik kentin adı') }}</title><path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" /></svg>
                </flux:label>
                <flux:input
                    wire:model="formSiteName"
                    placeholder="{{ __('Side Antik Kenti') }}"
                    required
                />
            </flux:field>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <flux:field>
                <flux:label>
                    {{ __('Konum') }}
                    <svg class="inline size-3.5 text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200 cursor-help ml-1" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><title>{{ __('İl veya ilçe bilgisi (Örn: Antalya, Manavgat)') }}</title><path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" /></svg>
                </flux:label>
                <flux:input
                    wire:model="formLocation"
                    placeholder="{{ __('Antalya, Manavgat') }}"
                />
            </flux:field>
            <flux:field>
                <flux:label>
                    {{ __('Ülke') }}
                    <svg class="inline size-3.5 text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200 cursor-help ml-1" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><title>{{ __('Kazı alanının bulunduğu ülke') }}</title><path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" /></svg>
                </flux:label>
                <flux:input
                    wire:model="formCountry"
                    placeholder="{{ __('Türkiye') }}"
                />
            </flux:field>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-start">
            <flux:field>
                <flux:label>
                    {{ __('Başlangıç Tarihi') }}
                    <svg class="inline size-3.5 text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200 cursor-help ml-1" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><title>{{ __('Kazı projesinin resmi başlangıç tarihi') }}</title><path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" /></svg>
                </flux:label>
                <flux:input
                    wire:model="formStartDate"
                    type="date"
                />
            </flux:field>
            <flux:field>
                <flux:label>
                    {{ __('Bitiş Tarihi') }}
                    <svg class="inline size-3.5 text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200 cursor-help ml-1" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><title>{{ __('Kazı tamamlandıysa bitiş tarihi, devam ediyorsa boş bırakın') }}</title><path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" /></svg>
                </flux:label>
                <flux:input
                    wire:model="formEndDate"
                    type="date"
                    hint="{{ __('Boş = devam') }}"
                />
            </flux:field>
            <flux:field>
                <flux:label>
                    {{ __('Kazı Başkanı') }}
                    <svg class="inline size-3.5 text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200 cursor-help ml-1" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><title>{{ __('Kazı başkanı olan akademisyen / bilim insanı unvanı ve adı') }}</title><path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" /></svg>
                </flux:label>
                <flux:input
                    wire:model="formDirector"
                    placeholder="{{ __('Prof. Dr. ...') }}"
                />
            </flux:field>
        </div>

        <flux:textarea
            wire:model="formDescription"
            label="{{ __('Açıklama') }}"
            placeholder="{{ __('Proje hakkında kısa bilgi...') }}"
            rows="3"
        />

        {{-- Yetkili Üyeler --}}
        <div class="border-t border-zinc-200 dark:border-zinc-700 pt-4 space-y-2">
            <flux:label class="font-semibold text-zinc-700 dark:text-zinc-300">
                <flux:icon icon="users" class="inline size-4 mr-1" />
                {{ __('Yetkili Üyeler (Kazı Ekibi)') }}
                <svg class="inline size-3.5 text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200 cursor-help ml-1" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><title>{{ __('Seçilen kullanıcılar bu kazı projesine ve projeye ait buluntu/sikkelere erişim sağlayabilir.') }}</title><path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" /></svg>
            </flux:label>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 max-h-36 overflow-y-auto p-2 bg-zinc-50 dark:bg-zinc-800/50 rounded-lg border border-zinc-200 dark:border-zinc-700">
                @foreach($this->allUsers as $u)
                    <label class="flex items-center gap-2 text-xs text-zinc-700 dark:text-zinc-300 cursor-pointer hover:bg-zinc-100 dark:hover:bg-zinc-700/50 p-1 rounded">
                        <input type="checkbox" wire:model="assignedUsers" value="{{ $u->id }}" class="rounded text-amber-600 focus:ring-amber-500 border-zinc-300 dark:border-zinc-600" />
                        <span class="truncate">{{ $u->name }}</span>
                    </label>
                @endforeach
            </div>
            <p class="text-xs text-zinc-400">{{ __('Not: Super Admin ve Admin rolleri her zaman tüm projelere tam erişim yetkisine sahiptir.') }}</p>
        </div>

        {{-- Görünür Opsiyonel Alanlar --}}
        <div x-data="{ openConfig: false }" class="border-t border-zinc-200 dark:border-zinc-700 pt-4">
            <button type="button" @click="openConfig = !openConfig"
                class="w-full flex items-center justify-between py-2 px-3 bg-zinc-100 dark:bg-zinc-800 rounded-lg text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">
                <div class="flex items-center gap-2">
                    <flux:icon icon="adjustments-horizontal" class="size-4 text-amber-500" />
                    <span>{{ __('Form Alan Yapılandırması (Aç / Kapa)') }}</span>
                </div>
                <flux:icon icon="chevron-down" class="size-4 text-zinc-400 transition-transform" ::class="openConfig ? 'rotate-180' : ''" />
            </button>

            <div x-show="openConfig" x-collapse class="mt-3 space-y-4">
                <div class="flex items-center justify-between bg-zinc-50 dark:bg-zinc-800/40 p-2 rounded-lg text-xs">
                    <span class="text-zinc-500 dark:text-zinc-400">{{ __('Buluntu ve sikke formlarında hangi opsiyonel alanların görüneceğini seçin:') }}</span>
                    <div class="flex gap-2">
                        <button type="button" wire:click="selectAllFields" class="text-amber-600 dark:text-amber-400 hover:underline font-medium">{{ __('Tümünü Seç') }}</button>
                        <span class="text-zinc-300">|</span>
                        <button type="button" wire:click="deselectAllFields" class="text-zinc-500 hover:underline">{{ __('Tümünü Temizle') }}</button>
                    </div>
                </div>

                {{-- Buluntu Alanları --}}
                <div>
                    <h5 class="text-xs font-semibold text-zinc-600 dark:text-zinc-400 mb-2 flex items-center gap-1.5">
                        <flux:icon icon="archive-box" class="size-3.5 text-blue-500" />
                        {{ __('Buluntu Opsiyonel Alanları') }}
                    </h5>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-1.5 max-h-48 overflow-y-auto p-2 bg-zinc-50 dark:bg-zinc-800/50 rounded-lg border border-zinc-200 dark:border-zinc-700">
                        @foreach(\App\Livewire\Pages\ExcavationProjects\Index::$availableFindFields as $fKey => $fLabel)
                            <label class="flex items-center gap-2 text-xs text-zinc-700 dark:text-zinc-300 cursor-pointer hover:bg-zinc-100 dark:hover:bg-zinc-700/50 p-1 rounded">
                                <input type="checkbox" wire:model="visibleFields" value="{{ $fKey }}" class="rounded text-amber-600 focus:ring-amber-500 border-zinc-300 dark:border-zinc-600" />
                                <span class="truncate">{{ __($fLabel) }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                {{-- Sikke Alanları --}}
                <div>
                    <h5 class="text-xs font-semibold text-zinc-600 dark:text-zinc-400 mb-2 flex items-center gap-1.5">
                        <flux:icon icon="circle-stack" class="size-3.5 text-amber-500" />
                        {{ __('Sikke Opsiyonel Alanları') }}
                    </h5>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-1.5 max-h-48 overflow-y-auto p-2 bg-zinc-50 dark:bg-zinc-800/50 rounded-lg border border-zinc-200 dark:border-zinc-700">
                        @foreach(\App\Livewire\Pages\ExcavationProjects\Index::$availableCoinFields as $cKey => $cLabel)
                            <label class="flex items-center gap-2 text-xs text-zinc-700 dark:text-zinc-300 cursor-pointer hover:bg-zinc-100 dark:hover:bg-zinc-700/50 p-1 rounded">
                                <input type="checkbox" wire:model="visibleFields" value="{{ $cKey }}" class="rounded text-amber-600 focus:ring-amber-500 border-zinc-300 dark:border-zinc-600" />
                                <span class="truncate">{{ __($cLabel) }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <flux:checkbox wire:model="formIsActive" label="{{ __('Aktif proje') }}" />

        <div class="flex justify-end gap-3 pt-2">
            <flux:button variant="ghost" wire:click="$set('showProjectModal', false)">{{ __('İptal') }}</flux:button>
            <flux:button type="submit" variant="primary">
                {{ $editingId ? __('Güncelle') : __('Oluştur') }}
            </flux:button>
        </div>
    </form>
</flux:modal>

{{-- Silme Onay --}}
<flux:modal wire:model="showDeleteModal" class="max-w-sm">
    <div class="space-y-4">
        <flux:heading size="lg">{{ __('Projeyi Sil') }}</flux:heading>
        <flux:text class="text-zinc-500">
            {{ __('Bu projeyi silmek istediğinizden emin misiniz? Altında buluntu veya sikke varsa silinemez.') }}
        </flux:text>
        <div class="flex justify-end gap-3">
            <flux:button variant="ghost" wire:click="$set('showDeleteModal', false)">{{ __('Vazgeç') }}</flux:button>
            <flux:button variant="danger" wire:click="delete">{{ __('Sil') }}</flux:button>
        </div>
    </div>
</flux:modal>
</div>
