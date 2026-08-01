@php
    $navColors = nav_colors();
    $permission = $item['permission'] ?? null;
    $visible = empty($permission) || (is_array($permission)
        ? auth()->user()->canAny($permission)
        : auth()->user()->can($permission));

    $children = collect($item['children'] ?? [])->filter(function (array $child) {
        $permission = $child['permission'] ?? null;
        return empty($permission) || (is_array($permission)
            ? auth()->user()->canAny($permission)
            : auth()->user()->can($permission));
    });

    $isActive = $children->contains(fn (array $child) => request()->routeIs(...(array) ($child['active'] ?? [$child['route'] ?? null])));
    $color = $item['color'] ?? 'brand';
@endphp
@if($visible && $children->isNotEmpty())
    <div x-data="{ open: {{ $isActive ? 'true' : 'false' }} }" class="space-y-1 pt-1">
        <button @click="open = !open" type="button" class="w-full flex items-center justify-between px-3 py-2 text-sm font-medium rounded-lg text-zinc-600 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-800/60 transition-colors cursor-pointer">
            <div class="flex items-center gap-2.5">
                <span class="flex h-7 w-7 items-center justify-center rounded-lg {{ $navColors['soft'][$color] ?? $navColors['soft']['brand'] }} shadow-2xs">
                    <flux:icon :icon="$item['icon'] ?? 'folder'" class="size-4" />
                </span>
                <span class="font-semibold text-zinc-800 dark:text-zinc-200">{{ $item['title'] }}</span>
            </div>
            <flux:icon icon="chevron-down" class="size-3.5 transition-transform duration-200 text-zinc-400" ::class="open ? 'rotate-180' : ''" />
        </button>

        <div x-show="open" x-collapse class="pl-4 space-y-1 border-l-2 border-brand/20 ml-6 my-1.5">
            @foreach($children as $child)
                @php
                    $childPermission = $child['permission'] ?? null;
                    $childVisible = empty($childPermission) || (is_array($childPermission)
                        ? auth()->user()->canAny($childPermission)
                        : auth()->user()->can($childPermission));
                @endphp
                @if($childVisible)
                    <flux:sidebar.item
                        :icon="$child['icon'] ?? 'folder'"
                        :href="route($child['route'])"
                        :current="request()->routeIs(...(array) ($child['active'] ?? [$child['route']]))"
                        wire:navigate
                    >
                        {{ $child['title'] }}
                    </flux:sidebar.item>
                @endif
            @endforeach
        </div>
    </div>
@endif
