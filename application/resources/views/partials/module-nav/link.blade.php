@php
    $navColors = nav_colors();
    $permission = $item['permission'] ?? null;
    $visible = empty($permission) || (is_array($permission)
        ? auth()->user()->canAny($permission)
        : auth()->user()->can($permission));

    $isActive = request()->routeIs(...(array) ($item['active'] ?? [$item['route']]));
    $color = $item['color'] ?? 'brand';
    $solid = (bool) ($item['solid'] ?? false);
@endphp
@if($visible)
    <a href="{{ route($item['route']) }}" wire:navigate class="w-full flex items-center justify-between px-3 py-2 text-sm font-medium rounded-lg transition-colors cursor-pointer {{ $isActive ? 'bg-zinc-200/60 dark:bg-zinc-800/80 text-zinc-900 dark:text-white' : 'text-zinc-600 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-800/60' }}">
        <div class="flex items-center gap-2.5">
            <span class="flex h-7 w-7 items-center justify-center rounded-lg {{ $solid ? $navColors['solid'][$color] ?? $navColors['solid']['brand'] : $navColors['soft'][$color] ?? $navColors['soft']['brand'] }} {{ $solid ? 'shadow-xs' : 'shadow-2xs' }}">
                <flux:icon :icon="$item['icon'] ?? 'folder'" class="size-4" />
            </span>
            <span class="font-semibold {{ $solid ? $navColors['title'][$color] ?? 'text-zinc-800 dark:text-zinc-200' : 'text-zinc-800 dark:text-zinc-200' }}">{{ $item['title'] }}</span>
        </div>
    </a>
@endif
