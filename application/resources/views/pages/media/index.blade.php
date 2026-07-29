<?php

use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Flux\Flux;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\MediaShare;

new #[Title('Media Library')] #[Layout('layouts.app')] class extends Component {
    use WithFileUploads, WithPagination;

    /** @var array<mixed> */
    public array $uploads = [];
    public string $search = '';
    public string $typeFilter = 'all';
    public ?array $selectedMedia = null;
    public bool $showUploadModal = false;
    public bool $showDetailModal = false;

    // Share Modal States
    public bool $showShareModal = false;
    public bool $enableSharePassword = false;
    public string $sharePassword = '';
    public string $shareExpiresIn = '';
    public ?string $generatedShareUrl = null;

    public function mount(): void
    {
        abort_unless(auth()->user()->can('media.view') || auth()->user()->hasRole('super-admin'), 403);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingTypeFilter(): void
    {
        $this->resetPage();
    }

    public function saveUploads(): void
    {
        abort_unless(auth()->user()->can('media.create') || auth()->user()->hasRole('super-admin'), 403);

        $this->validate([
            'uploads.*' => ['required', 'file', 'max:20480'], // max 20MB per file
        ]);

        $disk = Storage::disk('public');
        $uploadedCount = 0;

        foreach ($this->uploads as $file) {
            $originalName = $file->getClientOriginalName();
            $filename = Str::slug(pathinfo($originalName, PATHINFO_FILENAME)) . '_' . time() . '.' . $file->getClientOriginalExtension();
            $file->storeAs('media', $filename, 'public');
            $uploadedCount++;
        }

        \App\Models\ActivityLog::record(
            event: 'media_uploaded',
            description: __(':count files uploaded to Media Library.', ['count' => $uploadedCount])
        );

        $this->reset('uploads');
        $this->showUploadModal = false;

        Flux::toast(variant: 'success', text: __(':count files uploaded successfully.', ['count' => $uploadedCount]));
    }

    public function deleteFile(string $filename): void
    {
        abort_unless(auth()->user()->can('media.delete') || auth()->user()->hasRole('super-admin'), 403);

        $path = "media/{$filename}";
        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
            MediaShare::where('file_path', $path)->delete();

            \App\Models\ActivityLog::record(
                event: 'media_deleted',
                description: "Medya dosyası silindi: {$filename}"
            );

            $this->showDetailModal = false;
            $this->showShareModal = false;
            $this->selectedMedia = null;

            Flux::toast(variant: 'success', text: __('File deleted successfully.'));
        } else {
            Flux::toast(variant: 'danger', text: __('File not found.'));
        }
    }

    public function viewDetails(array $media): void
    {
        $this->selectedMedia = $media;
        $this->showDetailModal = true;
    }

    public function openShareModal(array $media): void
    {
        $this->selectedMedia = $media;
        $this->enableSharePassword = false;
        $this->sharePassword = '';
        $this->shareExpiresIn = '';
        $this->generatedShareUrl = null;
        $this->showShareModal = true;
    }

    public function generateShareLink(): void
    {
        if (!$this->selectedMedia) return;

        $expiresInDays = match($this->shareExpiresIn) {
            '1' => 1,
            '7' => 7,
            '30' => 30,
            default => null,
        };

        $password = $this->enableSharePassword && !empty($this->sharePassword) ? $this->sharePassword : null;
        $share = MediaShare::createShare($this->selectedMedia['path'], $password, $expiresInDays);

        $this->generatedShareUrl = route('media.share.public', ['token' => $share->share_token]);

        \App\Models\ActivityLog::record(
            event: 'media_shared',
            description: "Paylaşım bağlantısı oluşturuldu: {$this->selectedMedia['filename']}",
            properties: ['is_password_protected' => !empty($password), 'expires_in_days' => $expiresInDays]
        );

        Flux::toast(variant: 'success', text: __('Paylaşım bağlantısı oluşturuldu!'));
    }

    public function deleteShareLink(int $shareId): void
    {
        MediaShare::where('id', $shareId)->delete();
        Flux::toast(variant: 'success', text: __('Paylaşım bağlantısı silindi.'));
    }

    public function with(): array
    {
        $disk = Storage::disk('public');
        $files = $disk->exists('media') ? $disk->files('media') : [];
        $mediaList = [];

        foreach ($files as $file) {
            $filename = basename($file);
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            $size = $disk->size($file);
            $lastModified = $disk->lastModified($file);
            $url = route('media.file', ['filename' => $filename]);

            // Determine media category
            $category = 'other';
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'])) {
                $category = 'image';
            } elseif (in_array($ext, ['pdf', 'doc', 'docx', 'txt', 'xlsx', 'csv'])) {
                $category = 'document';
            } elseif (in_array($ext, ['zip', 'rar', '7z', 'tar', 'gz'])) {
                $category = 'archive';
            }

            // Search filter
            if (!empty($this->search) && !str_contains(strtolower($filename), strtolower($this->search))) {
                continue;
            }

            // Category filter
            if ($this->typeFilter !== 'all' && $category !== $this->typeFilter) {
                continue;
            }

            $mediaList[] = [
                'filename' => $filename,
                'path' => $file,
                'url' => $url,
                'ext' => $ext,
                'size' => $size,
                'formatted_size' => $this->formatBytes($size),
                'category' => $category,
                'created_at' => $lastModified,
            ];
        }

        // Sort latest first
        usort($mediaList, fn($a, $b) => $b['created_at'] <=> $a['created_at']);

        // Fetch existing shares for selected media with views relation
        $activeShares = [];
        if ($this->selectedMedia) {
            $activeShares = MediaShare::with(['views.user'])->where('file_path', $this->selectedMedia['path'])->latest()->get();
        }

        return [
            'mediaItems' => $mediaList,
            'totalCount' => count($mediaList),
            'imageCount' => count(array_filter($mediaList, fn($m) => $m['category'] === 'image')),
            'documentCount' => count(array_filter($mediaList, fn($m) => $m['category'] === 'document')),
            'archiveCount' => count(array_filter($mediaList, fn($m) => $m['category'] === 'archive')),
            'activeShares' => $activeShares,
        ];
    }

    protected function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, 2) . ' ' . $units[$pow];
    }
}; ?>
<div>
    <div class="space-y-6">
        {{-- Header & Upload Action --}}
        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="xl" class="font-extrabold tracking-tight">{{ __('Media Library') }}</flux:heading>
                <flux:subheading>{{ __('Centralized visual file & media manager with drag-and-drop uploads.') }}</flux:subheading>
            </div>
            @canany(['media.create', 'super-admin'])
                <flux:button variant="primary" icon="cloud-arrow-up" wire:click="$set('showUploadModal', true)" class="bg-brand hover:bg-brand-hover text-white shadow-xs cursor-pointer px-4 py-2 text-sm">
                    {{ __('Upload Files') }}
                </flux:button>
            @endcanany
        </div>

        {{-- Category Tabs & Search Bar --}}
        <div class="rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-5 shadow-xs space-y-4">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-1.5 overflow-x-auto w-full sm:w-auto pb-1 sm:pb-0 border-b sm:border-b-0 border-zinc-200 dark:border-zinc-800">
                    <button
                        wire:click="$set('typeFilter', 'all')"
                        class="px-4 py-2 rounded-xl text-xs font-bold transition-all cursor-pointer flex items-center gap-2 {{ $typeFilter === 'all' ? 'bg-brand/15 text-brand dark:text-brand-accent border border-brand/20' : 'text-zinc-500 hover:text-zinc-800 dark:hover:text-white' }}"
                    >
                        <flux:icon icon="photo" class="size-4" />
                        <span>{{ __('All Media') }} ({{ $totalCount }})</span>
                    </button>
                    <button
                        wire:click="$set('typeFilter', 'image')"
                        class="px-4 py-2 rounded-xl text-xs font-bold transition-all cursor-pointer flex items-center gap-2 {{ $typeFilter === 'image' ? 'bg-brand/15 text-brand dark:text-brand-accent border border-brand/20' : 'text-zinc-500 hover:text-zinc-800 dark:hover:text-white' }}"
                    >
                        <flux:icon icon="camera" class="size-4" />
                        <span>{{ __('Images') }} ({{ $imageCount }})</span>
                    </button>
                    <button
                        wire:click="$set('typeFilter', 'document')"
                        class="px-4 py-2 rounded-xl text-xs font-bold transition-all cursor-pointer flex items-center gap-2 {{ $typeFilter === 'document' ? 'bg-brand/15 text-brand dark:text-brand-accent border border-brand/20' : 'text-zinc-500 hover:text-zinc-800 dark:hover:text-white' }}"
                    >
                        <flux:icon icon="document-text" class="size-4" />
                        <span>{{ __('Documents') }} ({{ $documentCount }})</span>
                    </button>
                    <button
                        wire:click="$set('typeFilter', 'archive')"
                        class="px-4 py-2 rounded-xl text-xs font-bold transition-all cursor-pointer flex items-center gap-2 {{ $typeFilter === 'archive' ? 'bg-brand/15 text-brand dark:text-brand-accent border border-brand/20' : 'text-zinc-500 hover:text-zinc-800 dark:hover:text-white' }}"
                    >
                        <flux:icon icon="archive-box" class="size-4" />
                        <span>{{ __('Archives') }} ({{ $archiveCount }})</span>
                    </button>
                </div>

                <div class="w-full sm:w-72">
                    <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" :placeholder="__('Search media files...')" />
                </div>
            </div>
        </div>

        {{-- Media Grid --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
            @forelse($mediaItems as $media)
                <div class="rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 overflow-hidden shadow-xs hover:border-brand/40 transition-all group flex flex-col justify-between">
                    {{-- Thumbnail / File Icon Area (Clickable to open modal) --}}
                    <div
                        wire:click="viewDetails({{ json_encode($media) }})"
                        class="h-36 bg-zinc-100 dark:bg-zinc-800/60 relative overflow-hidden flex items-center justify-center cursor-pointer group/thumb"
                    >
                        @if($media['category'] === 'image')
                            <img src="{{ $media['url'] }}" alt="{{ $media['filename'] }}" class="w-full h-full object-cover group-hover/thumb:scale-105 transition-transform duration-300" />
                        @elseif($media['category'] === 'document')
                            <div class="flex flex-col items-center gap-1 text-red-500 group-hover/thumb:scale-110 transition-transform">
                                <flux:icon icon="document-text" class="size-12" />
                                <span class="text-[10px] uppercase font-bold text-zinc-500">{{ $media['ext'] }}</span>
                            </div>
                        @else
                            <div class="flex flex-col items-center gap-1 text-amber-500 group-hover/thumb:scale-110 transition-transform">
                                <flux:icon icon="archive-box" class="size-12" />
                                <span class="text-[10px] uppercase font-bold text-zinc-500">{{ $media['ext'] }}</span>
                            </div>
                        @endif

                        {{-- Extension Badge --}}
                        <span class="absolute top-2 left-2 px-2 py-0.5 rounded-md bg-zinc-900/80 text-white text-[10px] font-mono font-bold uppercase backdrop-blur-xs">
                            {{ $media['ext'] }}
                        </span>

                        {{-- Hover Overlay Icon --}}
                        <div class="absolute inset-0 bg-black/30 opacity-0 group-hover/thumb:opacity-100 transition-opacity flex items-center justify-center text-white">
                            <flux:icon icon="eye" class="size-6 drop-shadow-md" />
                        </div>
                    </div>

                    {{-- Info & Actions --}}
                    <div class="p-3 space-y-2">
                        <div
                            wire:click="viewDetails({{ json_encode($media) }})"
                            class="font-semibold text-xs text-zinc-900 dark:text-white truncate cursor-pointer hover:text-brand transition-colors"
                            title="{{ $media['filename'] }}"
                        >
                            {{ $media['filename'] }}
                        </div>

                        <div class="flex items-center justify-between text-[11px] text-zinc-400">
                            <span>{{ $media['formatted_size'] }}</span>
                            <span>{{ \Carbon\Carbon::createFromTimestamp($media['created_at'])->toSystemFormat() }}</span>
                        </div>

                        <div class="flex items-center justify-between pt-1 border-t border-zinc-100 dark:border-zinc-800/80">
                            {{-- Share Link Button --}}
                            <button
                                type="button"
                                wire:click="openShareModal({{ json_encode($media) }})"
                                class="text-[11px] font-bold text-indigo-600 dark:text-indigo-400 hover:underline cursor-pointer flex items-center gap-1"
                                title="{{ __('Share File (Public / Password Protected)') }}"
                            >
                                <flux:icon icon="share" class="size-3" />
                                <span>{{ __('Share') }}</span>
                            </button>

                            <div class="flex items-center gap-1">
                                {{-- Open in New Tab --}}
                                <a href="{{ $media['url'] }}" target="_blank" rel="noopener noreferrer" title="{{ __('Open in New Tab') }}" class="p-1 text-zinc-400 hover:text-brand hover:bg-brand/10 rounded-lg transition-colors">
                                    <flux:icon icon="arrow-top-right-on-square" class="size-3.5" />
                                </a>

                                {{-- Open Modal --}}
                                <button type="button" wire:click="viewDetails({{ json_encode($media) }})" title="{{ __('Details & Preview') }}" class="p-1 text-zinc-400 hover:text-brand hover:bg-brand/10 rounded-lg transition-colors cursor-pointer">
                                    <flux:icon icon="eye" class="size-3.5" />
                                </button>

                                {{-- Delete --}}
                                @canany(['media.delete', 'super-admin'])
                                    <button type="button" wire:click="deleteFile('{{ $media['filename'] }}')" wire:confirm="{{ __('Are you sure you want to delete this file?') }}" title="{{ __('Delete') }}" class="p-1 text-red-400 hover:text-red-600 hover:bg-red-500/10 rounded-lg transition-colors cursor-pointer">
                                        <flux:icon icon="trash" class="size-3.5" />
                                    </button>
                                @endcanany
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-12 text-center space-y-3">
                    <div class="flex h-16 w-16 items-center justify-center rounded-3xl bg-brand/15 text-brand dark:text-brand-accent mx-auto">
                        <flux:icon icon="photo" class="size-8" />
                    </div>
                    <div class="font-bold text-zinc-900 dark:text-white text-base">{{ __('No media files found') }}</div>
                    <div class="text-xs text-zinc-500 max-w-sm mx-auto">{{ __('Upload files to your central media library for use across your application.') }}</div>
                </div>
            @endforelse
        </div>

        {{-- Share Link Modal --}}
        @if($selectedMedia)
            <flux:modal wire:model="showShareModal" class="max-w-2xl min-w-[550px] p-4">
                <div class="space-y-6">
                    <div class="flex items-start gap-4">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-indigo-500/15 text-indigo-600 dark:text-indigo-400 border border-indigo-500/20">
                            <flux:icon icon="share" class="size-6" />
                        </div>
                        <div>
                            <flux:heading size="xl" class="font-extrabold text-zinc-900 dark:text-white text-xl">
                                {{ __('Share File') }}
                            </flux:heading>
                            <flux:subheading class="text-xs text-zinc-500 mt-0.5 truncate max-w-md">
                                {{ $selectedMedia['filename'] }} ({{ $selectedMedia['formatted_size'] }})
                            </flux:subheading>
                        </div>
                    </div>

                    {{-- Form Options --}}
                    <div class="space-y-4 pt-2">
                        {{-- Password Protection Switch --}}
                        <div class="p-4 rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-zinc-50 dark:bg-zinc-800/40 space-y-3">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <flux:icon icon="lock-closed" class="size-4 text-amber-500" />
                                    <span class="font-bold text-xs text-zinc-900 dark:text-white">{{ __('Enable Password Protection?') }}</span>
                                </div>
                                <flux:switch wire:model.live="enableSharePassword" />
                            </div>

                            @if($enableSharePassword)
                                <div class="pt-2">
                                    <flux:input
                                        wire:model="sharePassword"
                                        type="password"
                                        icon="key"
                                        :placeholder="__('Set share password...')"
                                    />
                                </div>
                            @endif
                        </div>

                        {{-- Expiration Option --}}
                        <div class="grid grid-cols-2 gap-4">
                            <flux:select wire:model="shareExpiresIn" :label="__('Link Expiration Period')">
                                <flux:select.option value="">{{ __('Indefinite (Always Valid)') }}</flux:select.option>
                                <flux:select.option value="1">{{ __('Valid for 1 Day') }}</flux:select.option>
                                <flux:select.option value="7">{{ __('Valid for 7 Days') }}</flux:select.option>
                                <flux:select.option value="30">{{ __('Valid for 30 Days') }}</flux:select.option>
                            </flux:select>
                        </div>

                        {{-- Generate Button --}}
                        <div class="flex justify-end pt-1">
                            <flux:button variant="primary" wire:click="generateShareLink" icon="link" class="bg-indigo-600 hover:bg-indigo-700 text-white cursor-pointer px-5">
                                {{ __('Create Share Link') }}
                            </flux:button>
                        </div>

                        {{-- Result Generated Link --}}
                        @if($generatedShareUrl)
                            <div class="p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 space-y-2">
                                <div class="text-xs font-bold text-emerald-600 dark:text-emerald-400 flex items-center gap-1.5">
                                    <flux:icon icon="check-circle" class="size-4" />
                                    <span>{{ __('New Share Link Ready!') }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <flux:input value="{{ $generatedShareUrl }}" readonly class="font-mono text-xs flex-1 bg-white dark:bg-zinc-950" />
                                    <flux:button
                                        x-data
                                        @click="navigator.clipboard.writeText('{{ $generatedShareUrl }}'); Flux.toast({ variant: 'success', text: '{{ __('Share link copied to clipboard!') }}' })"
                                        variant="primary" icon="clipboard-document" class="cursor-pointer bg-emerald-600 hover:bg-emerald-700 text-white px-4"
                                    >
                                        {{ __('Copy') }}
                                    </flux:button>
                                </div>
                            </div>
                        @endif

                        {{-- Active Shares List --}}
                        @if(!empty($activeShares))
                            <div class="space-y-2 pt-3 border-t border-zinc-100 dark:border-zinc-800">
                                <flux:label class="font-bold text-xs uppercase text-zinc-400">{{ __('Active Share Links for This File') }} ({{ count($activeShares) }})</flux:label>

                                <div class="space-y-2 max-h-64 overflow-y-auto">
                                    @foreach($activeShares as $sh)
                                        @php
                                            $shareUrl = route('media.share.public', ['token' => $sh->share_token]);
                                        @endphp
                                        <div x-data="{ showLogs: false }" class="rounded-xl bg-zinc-50 dark:bg-zinc-800/60 border border-zinc-200 dark:border-zinc-800 overflow-hidden text-xs">
                                            <div class="flex items-center justify-between p-3">
                                                <div class="flex items-center gap-3">
                                                    @if($sh->isPasswordProtected())
                                                        <span class="px-2 py-0.5 rounded-md bg-amber-500/15 text-amber-600 dark:text-amber-400 border border-amber-500/20 font-bold flex items-center gap-1">
                                                            <flux:icon icon="lock-closed" class="size-3" />
                                                            <span>{{ __('Password Protected') }}</span>
                                                        </span>
                                                    @else
                                                        <span class="px-2 py-0.5 rounded-md bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 font-bold">
                                                            {{ __('Public') }}
                                                        </span>
                                                    @endif

                                                    <div class="font-mono text-[11px] text-zinc-600 dark:text-zinc-400 truncate max-w-xs">
                                                        {{ $shareUrl }}
                                                    </div>
                                                </div>

                                                <div class="flex items-center gap-3">
                                                    <button @click="showLogs = !showLogs" type="button" class="text-[11px] font-bold text-zinc-500 hover:text-zinc-800 dark:hover:text-white flex items-center gap-1 cursor-pointer">
                                                        <flux:icon icon="eye" class="size-3 text-indigo-500" />
                                                        <span>{{ $sh->views_count }} {{ __('views') }}</span>
                                                        <flux:icon icon="chevron-down" class="size-3 transition-transform duration-200" ::class="showLogs ? 'rotate-180' : ''" />
                                                    </button>

                                                    <button
                                                        x-data
                                                        @click="navigator.clipboard.writeText('{{ $shareUrl }}'); Flux.toast({ variant: 'success', text: '{{ __('Copied!') }}' })"
                                                        class="text-brand hover:underline font-bold text-xs cursor-pointer"
                                                    >
                                                        {{ __('Copy') }}
                                                    </button>
                                                    <button type="button" wire:click="deleteShareLink({{ $sh->id }})" class="text-red-500 hover:text-red-600 cursor-pointer">
                                                        <flux:icon icon="trash" class="size-3.5" />
                                                    </button>
                                                </div>
                                            </div>

                                            {{-- Expandable View Logs Table --}}
                                            <div x-show="showLogs" x-collapse class="border-t border-zinc-200 dark:border-zinc-700/60 bg-zinc-100/60 dark:bg-zinc-900/60 p-3 space-y-2">
                                                <div class="font-bold text-[11px] text-zinc-500 uppercase">{{ __('Access & Download Logs') }}:</div>
                                                @forelse($sh->views as $v)
                                                    <div class="flex items-center justify-between text-[11px] py-1 border-b border-zinc-200/50 dark:border-zinc-800/50 last:border-0">
                                                        <div class="flex items-center gap-2">
                                                            @if($v->action === 'downloaded')
                                                                <span class="px-1.5 py-0.5 rounded bg-blue-500/15 text-blue-600 dark:text-blue-400 font-bold text-[10px] flex items-center gap-1">
                                                                    <flux:icon icon="arrow-down-tray" class="size-3" />
                                                                    <span>{{ __('Downloaded') }}</span>
                                                                </span>
                                                            @else
                                                                <span class="px-1.5 py-0.5 rounded bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 font-bold text-[10px] flex items-center gap-1">
                                                                    <flux:icon icon="eye" class="size-3" />
                                                                    <span>{{ __('Viewed') }}</span>
                                                                </span>
                                                            @endif

                                                            <span class="font-semibold text-zinc-800 dark:text-zinc-200">
                                                                {{ $v->user ? $v->user->name : __('Guest Visitor') }}
                                                            </span>
                                                        </div>

                                                        <div class="flex items-center gap-3 text-zinc-400 font-mono">
                                                            <span>IP: {{ $v->ip_address ?: '-' }}</span>
                                                            <span>{{ $v->created_at->toSystemFormat() }}</span>
                                                        </div>
                                                    </div>
                                                @empty
                                                    <div class="text-[11px] text-zinc-400 italic py-1">{{ __('No view or download logs yet.') }}</div>
                                                @endforelse
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="flex justify-end pt-3 border-t border-zinc-100 dark:border-zinc-800">
                        <flux:button variant="filled" wire:click="$set('showShareModal', false)" class="cursor-pointer px-6">
                            {{ __('Close') }}
                        </flux:button>
                    </div>
                </div>
            </flux:modal>
        @endif

        {{-- Upload Modal --}}
        <flux:modal wire:model="showUploadModal" class="max-w-2xl min-w-[550px] p-4">
            <form wire:submit="saveUploads" class="space-y-6">
                <div class="flex items-start gap-4">
                    <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-brand/15 text-brand dark:text-brand-accent shadow-2xs border border-brand/20">
                        <flux:icon icon="cloud-arrow-up" class="size-7" />
                    </div>
                    <div>
                        <flux:heading size="xl" class="font-extrabold text-zinc-900 dark:text-white text-xl">
                            {{ __('Upload Media Files') }}
                        </flux:heading>
                        <flux:subheading class="text-xs text-zinc-500 mt-0.5">
                            {{ __('Drag and drop or select files to upload (Max 20MB per file).') }}
                        </flux:subheading>
                    </div>
                </div>

                {{-- Drag & Drop Area --}}
                <div class="space-y-4">
                    <div
                        x-data="{ isDragging: false }"
                        @dragover.prevent="isDragging = true"
                        @dragleave.prevent="isDragging = false"
                        @drop.prevent="isDragging = false"
                        :class="isDragging ? 'border-brand bg-brand/10' : 'border-zinc-300 dark:border-zinc-700 bg-zinc-50/50 dark:bg-zinc-800/40'"
                        class="border-2 border-dashed rounded-2xl p-8 text-center space-y-3 transition-all relative"
                    >
                        <input
                            type="file"
                            wire:model="uploads"
                            multiple
                            class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                        />

                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-brand/15 text-brand dark:text-brand-accent mx-auto">
                            <flux:icon icon="cloud-arrow-up" class="size-6" />
                        </div>

                        <div>
                            <span class="font-bold text-sm text-zinc-800 dark:text-zinc-200">{{ __('Click to upload') }}</span>
                            <span class="text-xs text-zinc-500">{{ __('or drag and drop files here') }}</span>
                        </div>
                        <div class="text-[11px] text-zinc-400 font-medium">PNG, JPG, WEBP, SVG, PDF, DOCX, ZIP (Max 20MB)</div>
                    </div>

                    @if(!empty($uploads))
                        <div class="space-y-2">
                            <flux:label class="font-bold text-xs uppercase text-zinc-400">{{ __('Selected Files for Upload') }} ({{ count($uploads) }}):</flux:label>
                            <div class="space-y-1.5 max-h-40 overflow-y-auto pr-1">
                                @foreach($uploads as $index => $uFile)
                                    <div class="flex items-center justify-between p-2.5 rounded-xl bg-zinc-100 dark:bg-zinc-800 text-xs font-semibold text-zinc-800 dark:text-zinc-200">
                                        <div class="flex items-center gap-2 truncate">
                                            <flux:icon icon="document" class="size-4 text-brand" />
                                            <span class="truncate">{{ $uFile->getClientOriginalName() }}</span>
                                        </div>
                                        <span class="text-zinc-400 text-[11px] font-mono">{{ round($uFile->getSize() / 1024, 1) }} KB</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                <div class="flex items-center justify-end gap-3 pt-5 border-t border-zinc-100 dark:border-zinc-800">
                    <flux:button variant="filled" wire:click="$set('showUploadModal', false)" class="cursor-pointer px-5">{{ __('Cancel') }}</flux:button>
                    <flux:button variant="primary" type="submit" icon="check" class="cursor-pointer bg-brand hover:bg-brand-hover text-white shadow-xs px-6 py-2" :disabled="empty($uploads)">
                        {{ __('Start Upload') }}
                    </flux:button>
                </div>
            </form>
        </flux:modal>

        {{-- Media Preview Modal --}}
        @if($selectedMedia)
            <flux:modal wire:model="showDetailModal" class="max-w-4xl min-w-[700px] p-4">
                <div class="space-y-6">
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-3">
                            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-brand/15 text-brand dark:text-brand-accent">
                                <flux:icon icon="photo" class="size-6" />
                            </div>
                            <div>
                                <flux:heading size="xl" class="font-extrabold text-zinc-900 dark:text-white truncate max-w-xl">
                                    {{ $selectedMedia['filename'] }}
                                </flux:heading>
                                <flux:subheading class="text-xs text-zinc-500">
                                    {{ $selectedMedia['formatted_size'] }} • {{ strtoupper($selectedMedia['ext']) }} • {{ \Carbon\Carbon::createFromTimestamp($selectedMedia['created_at'])->toSystemFormat() }}
                                </flux:subheading>
                            </div>
                        </div>
                    </div>

                    {{-- Dynamic Preview Display --}}
                    <div class="rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-zinc-950 overflow-hidden flex items-center justify-center min-h-[350px] max-h-[550px] relative">
                        @if($selectedMedia['category'] === 'image')
                            <img src="{{ $selectedMedia['url'] }}" alt="{{ $selectedMedia['filename'] }}" class="max-h-[550px] w-auto object-contain" />
                        @elseif($selectedMedia['ext'] === 'pdf')
                            <iframe src="{{ $selectedMedia['url'] }}" class="w-full h-[500px] border-0"></iframe>
                        @else
                            <div class="flex flex-col items-center gap-4 text-zinc-400 p-12 text-center">
                                <div class="flex h-20 w-20 items-center justify-center rounded-3xl bg-zinc-800/80 text-brand">
                                    <flux:icon icon="document-text" class="size-10" />
                                </div>
                                <div>
                                    <div class="font-bold text-white text-base">{{ $selectedMedia['filename'] }}</div>
                                    <div class="text-xs text-zinc-500 mt-1">{{ __('Canlı önizleme bu dosya formatı için kullanılamıyor.') }}</div>
                                </div>
                                <a href="{{ $selectedMedia['url'] }}" target="_blank" rel="noopener noreferrer" class="px-4 py-2 rounded-xl bg-brand hover:bg-brand-hover text-white text-xs font-bold transition-colors inline-flex items-center gap-2">
                                    <flux:icon icon="arrow-top-right-on-square" class="size-4" />
                                    <span>{{ __('Dosyayı İndir / Tarayıcıda Aç') }}</span>
                                </a>
                            </div>
                        @endif
                    </div>

                    {{-- Public URL Box & Actions --}}
                    <div class="space-y-2">
                        <flux:label class="font-bold text-xs text-zinc-700 dark:text-zinc-300">{{ __('Public URL') }}</flux:label>
                        <div class="flex items-center gap-2">
                            <flux:input value="{{ $selectedMedia['url'] }}" readonly class="font-mono text-xs flex-1" />
                            <flux:button
                                x-data
                                @click="navigator.clipboard.writeText('{{ $selectedMedia['url'] }}'); Flux.toast({ variant: 'success', text: '{{ __('URL copied to clipboard!') }}' })"
                                variant="primary" icon="clipboard-document" class="cursor-pointer bg-brand hover:bg-brand-hover text-white px-4"
                            >
                                {{ __('Copy') }}
                            </flux:button>
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-3 border-t border-zinc-100 dark:border-zinc-800">
                        @canany(['media.delete', 'super-admin'])
                            <flux:button variant="ghost" icon="trash" class="text-red-500 hover:bg-red-500/10 hover:text-red-600 cursor-pointer" wire:click="deleteFile('{{ $selectedMedia['filename'] }}')" wire:confirm="{{ __('Are you sure you want to delete this file?') }}">
                                {{ __('Delete File') }}
                            </flux:button>
                        @else
                            <div></div>
                        @endcanany

                        <div class="flex items-center gap-3">
                            <flux:button variant="primary" icon="share" wire:click="openShareModal({{ json_encode($selectedMedia) }})" class="bg-indigo-600 hover:bg-indigo-700 text-white cursor-pointer px-4">
                                {{ __('Paylaş') }}
                            </flux:button>
                            <a href="{{ $selectedMedia['url'] }}" target="_blank" rel="noopener noreferrer" class="px-4 py-2 rounded-xl bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200 dark:hover:bg-zinc-700 text-zinc-800 dark:text-white text-xs font-bold transition-colors inline-flex items-center gap-1.5">
                                <flux:icon icon="arrow-top-right-on-square" class="size-4" />
                                <span>{{ __('Yeni Sekmede Aç') }}</span>
                            </a>
                            <flux:button variant="filled" wire:click="$set('showDetailModal', false)" class="cursor-pointer px-6">
                                {{ __('Close') }}
                            </flux:button>
                        </div>
                    </div>
                </div>
            </flux:modal>
        @endif
    </div>
</div>
