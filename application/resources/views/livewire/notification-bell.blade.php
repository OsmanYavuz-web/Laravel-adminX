<div x-data="{ open: false }" class="relative">
    <button type="button" @click="open = !open; $wire.loadNotifications()" class="relative flex items-center justify-center w-10 h-10 rounded-lg hover:bg-zinc-800/5 dark:hover:bg-white/15 text-zinc-500 hover:text-zinc-800 dark:text-zinc-400 dark:hover:text-white transition-colors cursor-pointer">
        <flux:icon icon="bell" class="size-5" />
        <span x-show="$wire.unreadCount > 0" class="absolute -top-0.5 -right-0.5 flex items-center justify-center min-w-[18px] h-[18px] px-1 rounded-full bg-red-500 text-white text-[10px] font-bold leading-none" x-text="$wire.unreadCount"></span>
    </button>

    <div x-show="open" @click.outside="open = false" class="fixed top-3 right-3 z-50 w-80 max-h-[calc(100vh-1.5rem)] rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 shadow-xl overflow-hidden flex flex-col">
        <div class="flex items-center justify-between px-4 py-3 border-b border-zinc-200 dark:border-zinc-800 shrink-0">
            <span class="text-sm font-bold text-zinc-900 dark:text-white">{{ __('Notifications') }}</span>
            <button x-show="$wire.unreadCount > 0" type="button" wire:click="markAllAsRead" class="text-xs font-bold text-brand hover:underline cursor-pointer">{{ __('Mark all as read') }}</button>
        </div>

        <div class="overflow-y-auto divide-y divide-zinc-100 dark:divide-zinc-800">
            <template x-for="notif in $wire.notifications" :key="notif.id">
                <div class="flex items-start gap-3 px-4 py-3 hover:bg-zinc-50 dark:hover:bg-zinc-800/40 transition-colors" :class="{'bg-brand/5': !notif.read_at}">
                    <div class="shrink-0 mt-0.5">
                        <template x-if="notif.type === 'success'">
                            <flux:icon icon="check-circle" class="size-5 text-emerald-500" />
                        </template>
                        <template x-if="notif.type === 'warning'">
                            <flux:icon icon="exclamation-triangle" class="size-5 text-amber-500" />
                        </template>
                        <template x-if="notif.type === 'danger'">
                            <flux:icon icon="x-circle" class="size-5 text-red-500" />
                        </template>
                        <template x-if="notif.type === 'info'">
                            <flux:icon icon="information-circle" class="size-5 text-blue-500" />
                        </template>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="text-sm font-semibold text-zinc-900 dark:text-white truncate" x-text="notif.title"></div>
                        <div x-show="notif.body" class="text-xs text-zinc-500 mt-0.5 line-clamp-2" x-text="notif.body"></div>
                        <div class="flex items-center gap-2 mt-1">
                            <a x-show="notif.action_url" :href="notif.action_url" class="text-[11px] font-bold text-brand hover:underline" x-text="notif.action_label || '{{ __('View') }}'"></a>
                            <button x-show="!notif.read_at" type="button" wire:click="markAsRead(notif.id)" class="text-[11px] text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-300 cursor-pointer">{{ __('Mark read') }}</button>
                        </div>
                    </div>
                    <span class="text-[10px] text-zinc-400 shrink-0 whitespace-nowrap" x-text="notif.created_at ? new Date(notif.created_at).toLocaleDateString() : ''"></span>
                </div>
            </template>

            <div x-show="$wire.notifications.length === 0" class="px-4 py-8 text-center">
                <div class="flex justify-center mb-2">
                    <flux:icon icon="bell-slash" class="size-8 text-zinc-300 dark:text-zinc-600" />
                </div>
                <div class="text-sm text-zinc-500">{{ __('No notifications yet.') }}</div>
            </div>
        </div>
    </div>
</div>
