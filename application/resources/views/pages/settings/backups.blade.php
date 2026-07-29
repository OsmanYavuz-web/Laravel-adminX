<?php

use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Livewire\Component;
use App\Services\BackupService;
use App\Models\SystemSetting;
use Flux\Flux;
use Illuminate\Support\Facades\Storage;

new #[Title('Backups Management')] #[Layout('layouts.app')] class extends Component {
    public string $backupType = 'full';
    public bool $showBackupModal = false;
    public string $autoSchedule = 'disabled';

    public function mount(): void
    {
        abort_unless(auth()->user()->can('backups.view') || auth()->user()->hasRole('super-admin'), 403);
        $this->autoSchedule = SystemSetting::get('backup_schedule', 'disabled');
    }

    public function openBackupModal(): void
    {
        abort_unless(auth()->user()->can('backups.create') || auth()->user()->hasRole('super-admin'), 403);
        $this->backupType = 'full';
        $this->showBackupModal = true;
    }

    public function runBackup(BackupService $backupService): void
    {
        abort_unless(auth()->user()->can('backups.create') || auth()->user()->hasRole('super-admin'), 403);

        $filename = $backupService->createBackup($this->backupType);
        $this->showBackupModal = false;

        Flux::toast(variant: 'success', text: __('Backup created successfully: :filename', ['filename' => $filename]));
    }

    public function downloadBackup(string $filename)
    {
        abort_unless(auth()->user()->can('backups.view') || auth()->user()->hasRole('super-admin'), 403);

        $path = "backups/{$filename}";
        if (Storage::disk('local')->exists($path)) {
            return response()->download(Storage::disk('local')->path($path));
        }

        Flux::toast(variant: 'danger', text: __('Backup file not found.'));
    }

    public function deleteBackup(string $filename, BackupService $backupService): void
    {
        abort_unless(auth()->user()->can('backups.delete') || auth()->user()->hasRole('super-admin'), 403);

        if ($backupService->deleteBackup($filename)) {
            Flux::toast(variant: 'success', text: __('Backup file deleted successfully.'));
        } else {
            Flux::toast(variant: 'danger', text: __('Backup file not found.'));
        }
    }

    public function saveSchedule(): void
    {
        abort_unless(auth()->user()->can('backups.create') || auth()->user()->hasRole('super-admin'), 403);

        SystemSetting::set('backup_schedule', $this->autoSchedule, 'backups');
        Flux::toast(variant: 'success', text: __('Automated backup schedule updated.'));
    }

    public function with(BackupService $backupService): array
    {
        $backups = $backupService->listBackups();
        $totalSize = array_sum(array_column($backups, 'size'));

        return [
            'backups' => $backups,
            'totalCount' => count($backups),
            'totalSizeFormatted' => $this->formatBytes($totalSize),
            'lastBackup' => $backups[0] ?? null,
            'typeBadges' => [
                'full' => ['color' => 'bg-brand/15 text-brand dark:text-brand-accent border-brand/20', 'icon' => 'archive-box', 'label' => __('Full System')],
                'db' => ['color' => 'bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 border-emerald-500/20', 'icon' => 'circle-stack', 'label' => __('Database Only')],
                'files' => ['color' => 'bg-amber-500/15 text-amber-600 dark:text-amber-400 border-amber-500/20', 'icon' => 'folder', 'label' => __('Files Only')],
            ],
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
        {{-- Header & Actions --}}
        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="xl" class="font-extrabold tracking-tight">{{ __('Backups Management') }}</flux:heading>
                <flux:subheading>{{ __('Manage system database & storage backups with selective type options.') }}</flux:subheading>
            </div>
            @canany(['backups.create', 'super-admin'])
                <flux:button variant="primary" icon="plus" wire:click="openBackupModal" class="bg-brand hover:bg-brand-hover text-white shadow-xs cursor-pointer px-4 py-2 text-sm">
                    {{ __('Create Backup') }}
                </flux:button>
            @endcanany
        </div>

        {{-- Statistics & Auto-Schedule Row --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            <div class="rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-5 shadow-xs flex items-center gap-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-brand/15 text-brand dark:text-brand-accent">
                    <flux:icon icon="archive-box" class="size-6" />
                </div>
                <div>
                    <div class="text-xs text-zinc-500 font-semibold">{{ __('Total Backups') }}</div>
                    <div class="text-2xl font-extrabold text-zinc-900 dark:text-white">{{ $totalCount }}</div>
                </div>
            </div>

            <div class="rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-5 shadow-xs flex items-center gap-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-500/15 text-emerald-600 dark:text-emerald-400">
                    <flux:icon icon="circle-stack" class="size-6" />
                </div>
                <div>
                    <div class="text-xs text-zinc-500 font-semibold">{{ __('Total Backup Size') }}</div>
                    <div class="text-2xl font-extrabold text-zinc-900 dark:text-white">{{ $totalSizeFormatted }}</div>
                </div>
            </div>

            <div class="rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-5 shadow-xs flex items-center gap-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-amber-500/15 text-amber-600 dark:text-amber-400">
                    <flux:icon icon="clock" class="size-6" />
                </div>
                <div>
                    <div class="text-xs text-zinc-500 font-semibold">{{ __('Last Backup') }}</div>
                    <div class="text-sm font-bold text-zinc-900 dark:text-white">
                        {{ $lastBackup ? \Carbon\Carbon::createFromTimestamp($lastBackup['created_at'])->toSystemFormat() : __('Never') }}
                    </div>
                </div>
            </div>
        </div>

        {{-- Backups Table --}}
        <div class="rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 overflow-hidden shadow-xs">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-zinc-600 dark:text-zinc-400">
                    <thead class="bg-zinc-50 dark:bg-zinc-800/60 text-xs font-bold uppercase text-zinc-500 border-b border-zinc-200 dark:border-zinc-800">
                        <tr>
                            <th class="px-6 py-4">{{ __('Backup Type') }}</th>
                            <th class="px-6 py-4">{{ __('Filename') }}</th>
                            <th class="px-6 py-4">{{ __('Size') }}</th>
                            <th class="px-6 py-4">{{ __('Date / Time') }}</th>
                            <th class="px-6 py-4 text-right">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                        @forelse($backups as $b)
                            @php
                                $badge = $typeBadges[$b['type']] ?? $typeBadges['full'];
                            @endphp
                            <tr class="hover:bg-zinc-50/60 dark:hover:bg-zinc-800/40 transition-colors">
                                <td class="px-6 py-4">
                                    <span class="px-3 py-1 rounded-full text-xs font-bold border flex items-center gap-1.5 w-fit {{ $badge['color'] }}">
                                        <flux:icon :icon="$badge['icon']" class="size-3.5" />
                                        <span>{{ $badge['label'] }}</span>
                                    </span>
                                </td>
                                <td class="px-6 py-4 font-mono text-xs font-semibold text-zinc-900 dark:text-white">
                                    {{ $b['filename'] }}
                                </td>
                                <td class="px-6 py-4 text-xs font-bold text-zinc-700 dark:text-zinc-300">
                                    {{ $b['formatted_size'] }}
                                </td>
                                <td class="px-6 py-4 text-xs text-zinc-500 font-medium">
                                    {{ \Carbon\Carbon::createFromTimestamp($b['created_at'])->toSystemFormat() }}
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <flux:button variant="filled" icon="arrow-down-tray" size="sm" wire:click="downloadBackup('{{ $b['filename'] }}')" class="hover:bg-brand/10 hover:text-brand cursor-pointer">
                                            {{ __('Download') }}
                                        </flux:button>
                                        @canany(['backups.delete', 'super-admin'])
                                            <flux:button variant="ghost" icon="trash" size="sm" class="text-red-500 hover:bg-red-500/10 hover:text-red-600 cursor-pointer" wire:click="deleteBackup('{{ $b['filename'] }}')" wire:confirm="{{ __('Are you sure you want to delete this backup file?') }}" />
                                        @endcanany
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-10 text-center text-zinc-400 italic">
                                    {{ __('No backup archives created yet. Click Create Backup to generate one.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Create Backup Modal --}}
        <flux:modal wire:model="showBackupModal" class="max-w-xl min-w-[500px] p-4">
            <form wire:submit="runBackup" class="space-y-6">
                <div class="flex items-start gap-4">
                    <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-brand/15 text-brand dark:text-brand-accent shadow-2xs border border-brand/20">
                        <flux:icon icon="archive-box" class="size-7" />
                    </div>
                    <div>
                        <flux:heading size="xl" class="font-extrabold text-zinc-900 dark:text-white text-xl">
                            {{ __('Create New Backup') }}
                        </flux:heading>
                        <flux:subheading class="text-xs text-zinc-500 mt-0.5">
                            {{ __('Select backup scope: Database only, Files only, or Full system archive.') }}
                        </flux:subheading>
                    </div>
                </div>

                <div class="space-y-3 pt-2">
                    <flux:label class="font-bold text-sm text-zinc-800 dark:text-zinc-200">{{ __('Select Backup Type') }}</flux:label>

                    <div class="grid grid-cols-1 gap-3">
                        {{-- Database Only --}}
                        <label class="flex items-center justify-between p-4 rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-zinc-50/50 dark:bg-zinc-800/40 hover:border-brand/40 has-[:checked]:bg-brand/10 has-[:checked]:border-brand transition-all cursor-pointer group">
                            <div class="flex items-center gap-3.5">
                                <input type="radio" wire:model="backupType" value="db" class="text-brand focus:ring-brand dark:bg-zinc-900 border-zinc-300 dark:border-zinc-700" />
                                <div class="flex items-center gap-2.5">
                                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-500/15 text-emerald-600 dark:text-emerald-400">
                                        <flux:icon icon="circle-stack" class="size-5" />
                                    </span>
                                    <div>
                                        <div class="font-bold text-sm text-zinc-900 dark:text-white group-hover:text-brand transition-colors">{{ __('Database Backup') }}</div>
                                        <div class="text-xs text-zinc-500">{{ __('Dumps and archives SQL database data only.') }}</div>
                                    </div>
                                </div>
                            </div>
                        </label>

                        {{-- Storage Files Only --}}
                        <label class="flex items-center justify-between p-4 rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-zinc-50/50 dark:bg-zinc-800/40 hover:border-brand/40 has-[:checked]:bg-brand/10 has-[:checked]:border-brand transition-all cursor-pointer group">
                            <div class="flex items-center gap-3.5">
                                <input type="radio" wire:model="backupType" value="files" class="text-brand focus:ring-brand dark:bg-zinc-900 border-zinc-300 dark:border-zinc-700" />
                                <div class="flex items-center gap-2.5">
                                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-500/15 text-amber-600 dark:text-amber-400">
                                        <flux:icon icon="folder" class="size-5" />
                                    </span>
                                    <div>
                                        <div class="font-bold text-sm text-zinc-900 dark:text-white group-hover:text-brand transition-colors">{{ __('Files & Media Backup') }}</div>
                                        <div class="text-xs text-zinc-500">{{ __('Archives uploaded storage media files only.') }}</div>
                                    </div>
                                </div>
                            </div>
                        </label>

                        {{-- Full System --}}
                        <label class="flex items-center justify-between p-4 rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-zinc-50/50 dark:bg-zinc-800/40 hover:border-brand/40 has-[:checked]:bg-brand/10 has-[:checked]:border-brand transition-all cursor-pointer group">
                            <div class="flex items-center gap-3.5">
                                <input type="radio" wire:model="backupType" value="full" class="text-brand focus:ring-brand dark:bg-zinc-900 border-zinc-300 dark:border-zinc-700" />
                                <div class="flex items-center gap-2.5">
                                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-brand/15 text-brand dark:text-brand-accent">
                                        <flux:icon icon="archive-box" class="size-5" />
                                    </span>
                                    <div>
                                        <div class="font-bold text-sm text-zinc-900 dark:text-white group-hover:text-brand transition-colors">{{ __('Full System Backup (Both)') }}</div>
                                        <div class="text-xs text-zinc-500">{{ __('Archives both database dump and uploaded storage files.') }}</div>
                                    </div>
                                </div>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-5 border-t border-zinc-100 dark:border-zinc-800">
                    <flux:button variant="filled" wire:click="$set('showBackupModal', false)" class="cursor-pointer px-5">{{ __('Cancel') }}</flux:button>
                    <flux:button variant="primary" type="submit" icon="check" class="cursor-pointer bg-brand hover:bg-brand-hover text-white shadow-xs px-6 py-2">{{ __('Generate Backup') }}</flux:button>
                </div>
            </form>
        </flux:modal>
    </div>
</div>
