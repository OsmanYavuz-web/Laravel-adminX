<?php

use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\ActivityLog;
use Flux\Flux;

new #[Title('Activity Logs')] #[Layout('layouts.app')] class extends Component {
    use WithPagination;

    public string $search = '';
    public string $eventFilter = '';
    public ?ActivityLog $selectedLog = null;
    public bool $showDetailModal = false;

    public function mount(): void
    {
        abort_unless(auth()->user()->can('logs.view') || auth()->user()->hasRole('super-admin'), 403);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingEventFilter(): void
    {
        $this->resetPage();
    }

    public function viewDetails(int $id): void
    {
        $this->selectedLog = ActivityLog::with('user')->findOrFail($id);
        $this->showDetailModal = true;
    }

    public function purgeOldLogs(): void
    {
        abort_unless(auth()->user()->can('logs.delete') || auth()->user()->hasRole('super-admin'), 403);

        $count = ActivityLog::where('created_at', '<', now()->subDays(30))->delete();
        Flux::toast(variant: 'success', text: __(':count old activity logs purged.', ['count' => $count]));
    }

    public function with(): array
    {
        $query = ActivityLog::with('user')->latest();

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('description', 'like', "%{$this->search}%")
                  ->orWhere('ip_address', 'like', "%{$this->search}%")
                  ->orWhereHas('user', function ($uq) {
                      $uq->where('name', 'like', "%{$this->search}%")
                         ->orWhere('email', 'like', "%{$this->search}%");
                  });
            });
        }

        if (!empty($this->eventFilter)) {
            $query->where('event', $this->eventFilter);
        }

        return [
            'logs' => $query->paginate(15),
            'totalLogs' => ActivityLog::count(),
            'todayLogins' => ActivityLog::where('event', 'login')->whereDate('created_at', today())->count(),
            'modelChanges' => ActivityLog::whereIn('event', ['created', 'updated', 'deleted'])->count(),
            'failedLogins' => ActivityLog::where('event', 'failed_login')->count(),
            'eventBadges' => [
                'login' => ['color' => 'bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 border-emerald-500/20', 'icon' => 'arrow-right-end-on-rectangle', 'label' => __('Login')],
                'logout' => ['color' => 'bg-zinc-500/15 text-zinc-600 dark:text-zinc-400 border-zinc-500/20', 'icon' => 'arrow-right-start-on-rectangle', 'label' => __('Logout')],
                'failed_login' => ['color' => 'bg-red-500/15 text-red-600 dark:text-red-400 border-red-500/20', 'icon' => 'exclamation-triangle', 'label' => __('Failed Login')],
                'created' => ['color' => 'bg-blue-500/15 text-blue-600 dark:text-blue-400 border-blue-500/20', 'icon' => 'plus-circle', 'label' => __('Created')],
                'updated' => ['color' => 'bg-amber-500/15 text-amber-600 dark:text-amber-400 border-amber-500/20', 'icon' => 'pencil-square', 'label' => __('Updated')],
                'deleted' => ['color' => 'bg-rose-500/15 text-rose-600 dark:text-rose-400 border-rose-500/20', 'icon' => 'trash', 'label' => __('Deleted')],
                'media_uploaded' => ['color' => 'bg-indigo-500/15 text-indigo-600 dark:text-indigo-400 border-indigo-500/20', 'icon' => 'cloud-arrow-up', 'label' => __('Media Uploaded')],
                'media_deleted' => ['color' => 'bg-rose-500/15 text-rose-600 dark:text-rose-400 border-rose-500/20', 'icon' => 'trash', 'label' => __('Media Deleted')],
                'backup_created' => ['color' => 'bg-purple-500/15 text-purple-600 dark:text-purple-400 border-purple-500/20', 'icon' => 'archive-box', 'label' => __('Backup Created')],
                'backup_deleted' => ['color' => 'bg-rose-500/15 text-rose-600 dark:text-rose-400 border-rose-500/20', 'icon' => 'trash', 'label' => __('Backup Deleted')],
            ],
        ];
    }
}; ?>
<div>
    <div class="space-y-6">
        {{-- Header & Actions --}}
        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="xl" class="font-extrabold tracking-tight">{{ __('Activity Logs') }}</flux:heading>
                <flux:subheading>{{ __('Track user logins, model changes, and system audit events.') }}</flux:subheading>
            </div>
            @canany(['logs.delete', 'super-admin'])
                <flux:button variant="filled" icon="trash" wire:click="purgeOldLogs" wire:confirm="{{ __('Purge logs older than 30 days?') }}" class="text-red-600 dark:text-red-400 hover:bg-red-500/10 cursor-pointer">
                    {{ __('Purge Old Logs') }}
                </flux:button>
            @endcanany
        </div>

        {{-- Statistics Cards Grid --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-5 shadow-xs flex items-center gap-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-brand/15 text-brand dark:text-brand-accent">
                    <flux:icon icon="clipboard-document-list" class="size-6" />
                </div>
                <div>
                    <div class="text-xs text-zinc-500 font-semibold">{{ __('Total Logs') }}</div>
                    <div class="text-2xl font-extrabold text-zinc-900 dark:text-white">{{ number_format($totalLogs) }}</div>
                </div>
            </div>

            <div class="rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-5 shadow-xs flex items-center gap-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-500/15 text-emerald-600 dark:text-emerald-400">
                    <flux:icon icon="arrow-right-end-on-rectangle" class="size-6" />
                </div>
                <div>
                    <div class="text-xs text-zinc-500 font-semibold">{{ __('Today Logins') }}</div>
                    <div class="text-2xl font-extrabold text-zinc-900 dark:text-white">{{ number_format($todayLogins) }}</div>
                </div>
            </div>

            <div class="rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-5 shadow-xs flex items-center gap-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-amber-500/15 text-amber-600 dark:text-amber-400">
                    <flux:icon icon="pencil-square" class="size-6" />
                </div>
                <div>
                    <div class="text-xs text-zinc-500 font-semibold">{{ __('Model Changes') }}</div>
                    <div class="text-2xl font-extrabold text-zinc-900 dark:text-white">{{ number_format($modelChanges) }}</div>
                </div>
            </div>

            <div class="rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-5 shadow-xs flex items-center gap-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-red-500/15 text-red-600 dark:text-red-400">
                    <flux:icon icon="exclamation-triangle" class="size-6" />
                </div>
                <div>
                    <div class="text-xs text-zinc-500 font-semibold">{{ __('Failed Logins') }}</div>
                    <div class="text-2xl font-extrabold text-zinc-900 dark:text-white">{{ number_format($failedLogins) }}</div>
                </div>
            </div>
        </div>

        {{-- Filters & Table --}}
        <div class="rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 overflow-hidden shadow-xs space-y-4 p-6">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="w-full sm:w-80">
                    <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" :placeholder="__('Search description, IP, user...')" />
                </div>
                <div class="w-full sm:w-60">
                    <flux:select wire:model.live="eventFilter" :placeholder="__('Filter by Event')">
                        <flux:select.option value="">{{ __('All Events') }}</flux:select.option>
                        <flux:select.option value="login">{{ __('Login') }}</flux:select.option>
                        <flux:select.option value="logout">{{ __('Logout') }}</flux:select.option>
                        <flux:select.option value="failed_login">{{ __('Failed Login') }}</flux:select.option>
                        <flux:select.option value="created">{{ __('Created') }}</flux:select.option>
                        <flux:select.option value="updated">{{ __('Updated') }}</flux:select.option>
                        <flux:select.option value="deleted">{{ __('Deleted') }}</flux:select.option>
                        <flux:select.option value="media_uploaded">{{ __('Media Uploaded') }}</flux:select.option>
                        <flux:select.option value="backup_created">{{ __('Backup Created') }}</flux:select.option>
                    </flux:select>
                </div>
            </div>

            <div class="overflow-x-auto pt-2">
                <table class="w-full text-left text-sm text-zinc-600 dark:text-zinc-400">
                    <thead class="bg-zinc-50 dark:bg-zinc-800/60 text-xs font-bold uppercase text-zinc-500 border-b border-zinc-200 dark:border-zinc-800">
                        <tr>
                            <th class="px-5 py-3.5">{{ __('Event') }}</th>
                            <th class="px-5 py-3.5">{{ __('User') }}</th>
                            <th class="px-5 py-3.5">{{ __('Description') }}</th>
                            <th class="px-5 py-3.5">{{ __('IP Address') }}</th>
                            <th class="px-5 py-3.5">{{ __('Date / Time') }}</th>
                            <th class="px-5 py-3.5 text-right">{{ __('Details') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                        @forelse($logs as $log)
                            @php
                                $badge = $eventBadges[$log->event] ?? ['color' => 'bg-zinc-500/15 text-zinc-600 border-zinc-500/20', 'icon' => 'information-circle', 'label' => ucfirst($log->event)];
                            @endphp
                            <tr class="hover:bg-zinc-50/60 dark:hover:bg-zinc-800/40 transition-colors">
                                <td class="px-5 py-3.5">
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold border flex items-center gap-1.5 w-fit {{ $badge['color'] }}">
                                        <flux:icon :icon="$badge['icon']" class="size-3.5" />
                                        <span>{{ $badge['label'] }}</span>
                                    </span>
                                </td>
                                <td class="px-5 py-3.5">
                                    @if($log->user)
                                        <div class="flex items-center gap-2.5">
                                            <flux:avatar :name="$log->user->name" :initials="$log->user->initials()" size="xs" />
                                            <div>
                                                <div class="font-bold text-zinc-900 dark:text-white text-xs">{{ $log->user->name }}</div>
                                                <div class="text-[11px] text-zinc-400 font-mono">{{ $log->user->email }}</div>
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-xs text-zinc-400 italic">{{ __('Guest / System') }}</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5 text-zinc-800 dark:text-zinc-200 font-medium">
                                    {{ __($log->description) }}
                                </td>
                                <td class="px-5 py-3.5 font-mono text-xs text-zinc-500">
                                    {{ $log->ip_address ?: '-' }}
                                </td>
                                <td class="px-5 py-3.5 text-xs text-zinc-500 font-medium">
                                    <div>{{ $log->created_at->toSystemFormat() }}</div>
                                    <div class="text-[10px] text-zinc-400">{{ $log->created_at->diffForHumans() }}</div>
                                </td>
                                <td class="px-5 py-3.5 text-right">
                                    @if(!empty($log->properties))
                                        <flux:button variant="ghost" icon="eye" size="sm" wire:click="viewDetails({{ $log->id }})" class="hover:bg-brand/10 hover:text-brand cursor-pointer">
                                            {{ __('Diff') }}
                                        </flux:button>
                                    @else
                                        <span class="text-xs text-zinc-400">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-8 text-center text-zinc-400 italic">
                                    {{ __('No activity logs recorded yet.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="pt-2">
                {{ $logs->links() }}
            </div>
        </div>

        {{-- Detail Modal --}}
        @if($selectedLog)
            <flux:modal wire:model="showDetailModal" class="max-w-2xl min-w-[550px] p-4">
                <div class="space-y-5">
                    <div class="flex items-start gap-4">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-brand/15 text-brand dark:text-brand-accent border border-brand/20">
                            <flux:icon icon="clipboard-document-list" class="size-6" />
                        </div>
                        <div>
                            <flux:heading size="lg" class="font-extrabold text-zinc-900 dark:text-white">
                                {{ __('Log Details') }} #{{ $selectedLog->id }}
                            </flux:heading>
                            <flux:subheading class="text-xs text-zinc-500 mt-0.5">
                                {{ $selectedLog->description }}
                            </flux:subheading>
                        </div>
                    </div>

                    <div class="space-y-4 pt-2">
                        <div class="grid grid-cols-2 gap-4 bg-zinc-50 dark:bg-zinc-800/50 p-3.5 rounded-xl text-xs">
                            <div>
                                <span class="text-zinc-400 font-semibold">{{ __('User') }}:</span>
                                <span class="font-bold text-zinc-800 dark:text-zinc-200 ml-1.5">{{ $selectedLog->user?->name ?: __('Guest') }}</span>
                            </div>
                            <div>
                                <span class="text-zinc-400 font-semibold">{{ __('IP Address') }}:</span>
                                <span class="font-mono text-zinc-800 dark:text-zinc-200 ml-1.5">{{ $selectedLog->ip_address }}</span>
                            </div>
                            <div class="col-span-2">
                                <span class="text-zinc-400 font-semibold">{{ __('User Agent') }}:</span>
                                <span class="font-mono text-[11px] text-zinc-600 dark:text-zinc-400 ml-1.5 block truncate">{{ $selectedLog->user_agent }}</span>
                            </div>
                        </div>

                        {{-- Properties JSON / Diff --}}
                        <div class="space-y-2">
                            <flux:label class="font-bold text-xs uppercase text-zinc-400">{{ __('Changed Properties / Diff') }}</flux:label>
                            <pre class="bg-zinc-900 text-zinc-100 p-4 rounded-xl text-xs font-mono overflow-x-auto max-h-72 border border-zinc-800">{{ json_encode($selectedLog->properties, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                        </div>
                    </div>

                    <div class="flex justify-end pt-3">
                        <flux:button variant="filled" wire:click="$set('showDetailModal', false)" class="cursor-pointer px-5">
                            {{ __('Close') }}
                        </flux:button>
                    </div>
                </div>
            </flux:modal>
        @endif
    </div>
</div>
