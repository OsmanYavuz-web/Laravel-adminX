<?php

use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Flux\Flux;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Artisan;

new #[Title('System Information')] #[Layout('layouts.app')] class extends Component {
    public string $cacheResult = '';

    public function clearCache(string $type): void
    {
        abort_unless(auth()->user()->can('settings.update') || auth()->user()->hasRole('super-admin'), 403);

        $commands = [
            'config' => 'config:cache',
            'route' => 'route:cache',
            'view' => 'view:cache',
        ];

        if (isset($commands[$type])) {
            Artisan::call($commands[$type]);
            $this->cacheResult = __('Cache cleared successfully.');
            Flux::toast(variant: 'success', text: $this->cacheResult);
        }
    }

    public function with(): array
    {
        $disk = Storage::disk('local');
        $mediaFiles = $disk->exists('media') ? $disk->files('media') : [];
        $mediaTotalSize = array_sum(array_map(fn($f) => $disk->size($f), $mediaFiles));

        $dbPath = config('database.connections.' . config('database.default') . '.database');
        $dbSize = $dbPath && file_exists($dbPath) ? filesize($dbPath) : 0;

        $storagePath = storage_path();
        $storageSize = $this->dirSize($storagePath);

        $extensions = ['pdo', 'mbstring', 'xml', 'curl', 'gd', 'zip', 'json', 'fileinfo', 'openssl', 'tokenizer'];
        $loaded = get_loaded_extensions();

        return [
            'phpVersion' => phpversion(),
            'laravelVersion' => app()->version(),
            'environment' => app()->environment(),
            'debugMode' => config('app.debug'),
            'dbDriver' => config('database.default'),
            'dbSizeFormatted' => $this->formatBytes($dbSize),
            'mediaCount' => count($mediaFiles),
            'mediaSizeFormatted' => $this->formatBytes($mediaTotalSize),
            'storageSizeFormatted' => $this->formatBytes($storageSize),
            'uploadMaxFilesize' => ini_get('upload_max_filesize'),
            'postMaxSize' => ini_get('post_max_size'),
            'memoryLimit' => ini_get('memory_limit'),
            'maxExecutionTime' => ini_get('max_execution_time'),
            'maxInputVars' => ini_get('max_input_vars'),
            'serverSoftware' => $_SERVER['SERVER_SOFTWARE'] ?? 'N/A',
            'extensions' => collect($extensions)->map(fn($ext) => [
                'name' => $ext,
                'loaded' => in_array($ext, $loaded),
            ]),
            'timezone' => config('app.timezone'),
            'locale' => app()->getLocale(),
        ];
    }

    protected function dirSize(string $dir): int
    {
        $size = 0;
        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS)) as $file) {
            $size += $file->getSize();
        }
        return $size;
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
        <div>
            <flux:heading size="xl" class="font-extrabold tracking-tight">{{ __('System Information') }}</flux:heading>
            <flux:subheading>{{ __('View system configuration, PHP settings, and environment details.') }}</flux:subheading>
        </div>

        @if($cacheResult)
            <div class="p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-700 dark:text-emerald-300 text-sm font-bold">
                {{ $cacheResult }}
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-5 shadow-xs space-y-4">
                <div class="flex items-center justify-between">
                    <flux:heading size="lg">{{ __('Application') }}</flux:heading>
                    <flux:icon icon="server-stack" class="size-5 text-zinc-400" />
                </div>
                <div class="space-y-3">
                    <div class="flex items-center justify-between py-2 border-b border-zinc-100 dark:border-zinc-800">
                        <span class="text-sm text-zinc-500">{{ __('Laravel Version') }}</span>
                        <span class="text-sm font-bold text-zinc-900 dark:text-white">{{ $laravelVersion }}</span>
                    </div>
                    <div class="flex items-center justify-between py-2 border-b border-zinc-100 dark:border-zinc-800">
                        <span class="text-sm text-zinc-500">{{ __('Environment') }}</span>
                        <span class="px-2 py-0.5 rounded-md text-xs font-bold {{ $environment === 'production' ? 'bg-amber-500/15 text-amber-600' : 'bg-emerald-500/15 text-emerald-600' }}">{{ $environment }}</span>
                    </div>
                    <div class="flex items-center justify-between py-2 border-b border-zinc-100 dark:border-zinc-800">
                        <span class="text-sm text-zinc-500">{{ __('Debug Mode') }}</span>
                        <span class="px-2 py-0.5 rounded-md text-xs font-bold {{ $debugMode ? 'bg-amber-500/15 text-amber-600' : 'bg-emerald-500/15 text-emerald-600' }}">{{ $debugMode ? __('On') : __('Off') }}</span>
                    </div>
                    <div class="flex items-center justify-between py-2 border-b border-zinc-100 dark:border-zinc-800">
                        <span class="text-sm text-zinc-500">{{ __('Database') }}</span>
                        <span class="text-sm font-bold text-zinc-900 dark:text-white">{{ $dbDriver }} ({{ $dbSizeFormatted }})</span>
                    </div>
                    <div class="flex items-center justify-between py-2 border-b border-zinc-100 dark:border-zinc-800">
                        <span class="text-sm text-zinc-500">{{ __('Timezone') }}</span>
                        <span class="text-sm font-bold text-zinc-900 dark:text-white">{{ $timezone }}</span>
                    </div>
                    <div class="flex items-center justify-between py-2">
                        <span class="text-sm text-zinc-500">{{ __('Locale') }}</span>
                        <span class="text-sm font-bold text-zinc-900 dark:text-white">{{ $locale }}</span>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-5 shadow-xs space-y-4">
                <div class="flex items-center justify-between">
                    <flux:heading size="lg">{{ __('PHP & Server') }}</flux:heading>
                    <flux:icon icon="cog" class="size-5 text-zinc-400" />
                </div>
                <div class="space-y-3">
                    <div class="flex items-center justify-between py-2 border-b border-zinc-100 dark:border-zinc-800">
                        <span class="text-sm text-zinc-500">{{ __('PHP Version') }}</span>
                        <span class="text-sm font-bold text-zinc-900 dark:text-white">{{ $phpVersion }}</span>
                    </div>
                    <div class="flex items-center justify-between py-2 border-b border-zinc-100 dark:border-zinc-800">
                        <span class="text-sm text-zinc-500">{{ __('Server') }}</span>
                        <span class="text-sm font-bold text-zinc-900 dark:text-white truncate max-w-[200px]">{{ $serverSoftware }}</span>
                    </div>
                    <div class="flex items-center justify-between py-2 border-b border-zinc-100 dark:border-zinc-800">
                        <span class="text-sm text-zinc-500">upload_max_filesize</span>
                        <span class="text-sm font-bold text-zinc-900 dark:text-white">{{ $uploadMaxFilesize }}</span>
                    </div>
                    <div class="flex items-center justify-between py-2 border-b border-zinc-100 dark:border-zinc-800">
                        <span class="text-sm text-zinc-500">post_max_size</span>
                        <span class="text-sm font-bold text-zinc-900 dark:text-white">{{ $postMaxSize }}</span>
                    </div>
                    <div class="flex items-center justify-between py-2 border-b border-zinc-100 dark:border-zinc-800">
                        <span class="text-sm text-zinc-500">memory_limit</span>
                        <span class="text-sm font-bold text-zinc-900 dark:text-white">{{ $memoryLimit }}</span>
                    </div>
                    <div class="flex items-center justify-between py-2 border-b border-zinc-100 dark:border-zinc-800">
                        <span class="text-sm text-zinc-500">max_execution_time</span>
                        <span class="text-sm font-bold text-zinc-900 dark:text-white">{{ $maxExecutionTime }}s</span>
                    </div>
                    <div class="flex items-center justify-between py-2">
                        <span class="text-sm text-zinc-500">max_input_vars</span>
                        <span class="text-sm font-bold text-zinc-900 dark:text-white">{{ $maxInputVars }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-5 shadow-xs space-y-4">
                <div class="flex items-center justify-between">
                    <flux:heading size="lg">{{ __('Storage') }}</flux:heading>
                    <flux:icon icon="archive-box" class="size-5 text-zinc-400" />
                </div>
                <div class="space-y-3">
                    <div class="flex items-center justify-between py-2 border-b border-zinc-100 dark:border-zinc-800">
                        <span class="text-sm text-zinc-500">{{ __('Storage Total') }}</span>
                        <span class="text-sm font-bold text-zinc-900 dark:text-white">{{ $storageSizeFormatted }}</span>
                    </div>
                    <div class="flex items-center justify-between py-2 border-b border-zinc-100 dark:border-zinc-800">
                        <span class="text-sm text-zinc-500">{{ __('Media Files') }}</span>
                        <span class="text-sm font-bold text-zinc-900 dark:text-white">{{ $mediaCount }} ({{ $mediaSizeFormatted }})</span>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-5 shadow-xs space-y-4">
                <div class="flex items-center justify-between">
                    <flux:heading size="lg">{{ __('PHP Extensions') }}</flux:heading>
                    <flux:icon icon="puzzle-piece" class="size-5 text-zinc-400" />
                </div>
                <div class="grid grid-cols-2 gap-2">
                    @foreach($extensions as $ext)
                        <div class="flex items-center gap-2 py-1.5">
                            @if($ext['loaded'])
                                <flux:icon icon="check-circle" class="size-4 text-emerald-500 shrink-0" />
                            @else
                                <flux:icon icon="x-circle" class="size-4 text-red-500 shrink-0" />
                            @endif
                            <span class="text-sm {{ $ext['loaded'] ? 'text-zinc-900 dark:text-white' : 'text-zinc-400' }}">{{ $ext['name'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-5 shadow-xs space-y-4">
            <div class="flex items-center justify-between">
                <flux:heading size="lg">{{ __('Cache Management') }}</flux:heading>
                <flux:icon icon="bolt" class="size-5 text-amber-500" />
            </div>
            <div class="flex flex-wrap gap-3">
                <flux:button wire:click="clearCache('config')" icon="adjustments-horizontal" class="cursor-pointer">{{ __('Clear Config Cache') }}</flux:button>
                <flux:button wire:click="clearCache('route')" icon="map" class="cursor-pointer">{{ __('Clear Route Cache') }}</flux:button>
                <flux:button wire:click="clearCache('view')" icon="eye" class="cursor-pointer">{{ __('Clear View Cache') }}</flux:button>
                <form method="POST" action="{{ route('cache.clear-all') }}" class="inline">
                    @csrf
                    <flux:button type="submit" icon="trash" class="bg-amber-500 hover:bg-amber-600 text-white cursor-pointer">{{ __('Clear All Cache') }}</flux:button>
                </form>
            </div>
        </div>
    </div>
</div>
