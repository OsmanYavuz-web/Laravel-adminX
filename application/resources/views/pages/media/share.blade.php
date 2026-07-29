<?php

use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Livewire\Component;
use App\Models\MediaShare;
use App\Models\MediaShareView;
use Illuminate\Support\Facades\Storage;

new #[Title('Shared File')] #[Layout('layouts.share')] class extends Component {
    public string $token = '';
    public string $passwordInput = '';
    public bool $unlocked = false;
    public ?MediaShare $share = null;

    public function mount(string $token): void
    {
        $this->token = $token;
        $this->share = MediaShare::where('share_token', $token)->firstOrFail();

        // If not password protected and not expired, unlock automatically
        if (!$this->share->isPasswordProtected() && !$this->share->isExpired()) {
            $this->unlocked = true;
            $this->share->increment('views_count');
            MediaShareView::recordView($this->share, 'viewed');
        }
    }

    public function unlock(): void
    {
        $this->validate([
            'passwordInput' => ['required', 'string'],
        ]);

        if ($this->share->checkPassword($this->passwordInput)) {
            $this->unlocked = true;
            $this->share->increment('views_count');
            MediaShareView::recordView($this->share, 'viewed');
        } else {
            $this->addError('passwordInput', __('The password provided was incorrect. Please try again.'));
        }
    }

    public function download()
    {
        if (!$this->unlocked || $this->share->isExpired()) {
            abort(403);
        }

        MediaShareView::recordView($this->share, 'downloaded');

        $disk = Storage::disk('public');
        if ($disk->exists($this->share->file_path)) {
            return response()->download($disk->path($this->share->file_path));
        }

        abort(404);
    }

    public function with(): array
    {
        $fileInfo = null;
        if ($this->share && $this->unlocked && !$this->share->isExpired()) {
            $disk = Storage::disk('public');
            if ($disk->exists($this->share->file_path)) {
                $filename = basename($this->share->file_path);
                $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                $size = $disk->size($this->share->file_path);

                $category = 'other';
                if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'])) {
                    $category = 'image';
                } elseif (in_array($ext, ['pdf', 'doc', 'docx', 'txt'])) {
                    $category = 'document';
                }

                $fileInfo = [
                    'filename' => $filename,
                    'ext' => $ext,
                    'size' => $size,
                    'formatted_size' => $this->formatBytes($size),
                    'category' => $category,
                    'url' => route('media.file', ['filename' => $filename]),
                ];
            }
        }

        return [
            'fileInfo' => $fileInfo,
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
<div class="min-h-screen flex flex-col justify-between p-4 sm:p-8">
    {{-- Top Header Bar --}}
    <header class="w-full max-w-5xl mx-auto flex items-center justify-between py-2">
        <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand/20 text-brand border border-brand/30 shadow-md">
                <flux:icon icon="share" class="size-5" />
            </div>
            <span class="font-black text-lg text-white tracking-tight">{{ config('app.name', 'Laravel-adminX') }}</span>
        </div>

        {{-- Sleek Language Switcher --}}
        <div class="flex items-center p-1 rounded-xl bg-zinc-900 border border-zinc-800">
            @foreach(config('app.available_locales', []) as $code => $locale)
                <a
                    href="/locale/{{ $code }}"
                    class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all flex items-center gap-1.5 {{ app()->getLocale() === $code ? 'bg-brand text-white shadow-xs' : 'text-zinc-400 hover:text-white' }}"
                >
                    <span>{{ $locale['flag'] }}</span>
                    <span>{{ strtoupper($code) }}</span>
                </a>
            @endforeach
        </div>
    </header>

    {{-- Main Container Card --}}
    <main class="w-full max-w-lg mx-auto my-auto py-8">
        {{-- Case 1: Expired Share --}}
        @if($share->isExpired())
            <div class="rounded-3xl border border-red-500/30 bg-red-950/30 backdrop-blur-2xl p-8 sm:p-10 text-center space-y-5 shadow-2xl shadow-red-950/50">
                <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-red-500/20 text-red-400 border border-red-500/30 mx-auto shadow-inner">
                    <flux:icon icon="clock" class="size-8" />
                </div>
                <div class="space-y-1.5">
                    <h2 class="font-extrabold text-xl text-white">{{ __('Share Link Expired') }}</h2>
                    <p class="text-xs text-zinc-400 max-w-sm mx-auto">{{ __('This file sharing link has expired. Please contact the person who shared the file.') }}</p>
                </div>
            </div>

        {{-- Case 2: Password Protected & Locked --}}
        @elseif($share->isPasswordProtected() && !$unlocked)
            <div class="rounded-3xl border border-white/10 bg-zinc-900/90 backdrop-blur-xl p-8 sm:p-10 shadow-2xl shadow-black/80 space-y-8 relative overflow-hidden">
                {{-- Decorative Glow --}}
                <div class="absolute -top-24 -right-24 w-48 h-48 bg-amber-500/10 rounded-full blur-3xl pointer-events-none"></div>

                {{-- Header Info --}}
                <div class="flex flex-col items-center text-center space-y-3">
                    <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-amber-500/15 text-amber-400 border border-amber-500/25 shadow-lg shadow-amber-500/10">
                        <flux:icon icon="lock-closed" class="size-8" />
                    </div>
                    <div>
                        <h2 class="font-black text-2xl text-white tracking-tight">{{ __('Password Protected File') }}</h2>
                        <p class="text-xs text-zinc-400 mt-1 max-w-xs mx-auto">{{ __('Enter the password to access this file.') }}</p>
                    </div>
                </div>

                {{-- Password Form --}}
                <form wire:submit="unlock" class="space-y-5">
                    <div class="space-y-2">
                        <label class="block font-bold text-xs text-zinc-300 uppercase tracking-wider">{{ __('Share Password') }}</label>
                        <div class="relative">
                            <input
                                wire:model="passwordInput"
                                type="password"
                                placeholder="{{ __('Enter share password...') }}"
                                required
                                autofocus
                                class="w-full bg-zinc-950/90 border border-zinc-700/80 focus:border-amber-500 text-white placeholder-zinc-500 rounded-xl px-4 py-3.5 pl-11 text-sm font-medium transition-all outline-none focus:ring-2 focus:ring-amber-500/20"
                            />
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-zinc-400">
                                <flux:icon icon="key" class="size-5" />
                            </div>
                        </div>
                        @error('passwordInput')
                            <p class="text-xs text-red-400 font-semibold flex items-center gap-1 mt-1">
                                <flux:icon icon="exclamation-circle" class="size-3.5" />
                                <span>{{ $message }}</span>
                            </p>
                        @enderror
                    </div>

                    <flux:button variant="primary" type="submit" icon="lock-open" class="w-full bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-zinc-950 font-black py-4 text-sm rounded-xl shadow-xl shadow-amber-500/20 transition-all cursor-pointer">
                        {{ __('Unlock and View') }}
                    </flux:button>
                </form>
            </div>

        {{-- Case 3: Unlocked / Public File View --}}
        @elseif($fileInfo)
            <div class="rounded-3xl border border-white/10 bg-zinc-900/90 backdrop-blur-xl p-8 sm:p-10 shadow-2xl shadow-black/80 space-y-6 relative overflow-hidden">
                {{-- Decorative Glow --}}
                <div class="absolute -top-24 -right-24 w-48 h-48 bg-brand/15 rounded-full blur-3xl pointer-events-none"></div>

                {{-- Top Tags Bar --}}
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <span class="px-3 py-1 rounded-full text-xs font-mono font-black uppercase bg-brand/15 text-brand border border-brand/25">
                            {{ $fileInfo['ext'] }}
                        </span>
                        <span class="text-xs font-bold text-zinc-400">{{ $fileInfo['formatted_size'] }}</span>
                    </div>
                    <span class="text-xs text-zinc-500 font-mono flex items-center gap-1">
                        <flux:icon icon="eye" class="size-3.5" />
                        <span>{{ $share->views_count }} {{ __('views') }}</span>
                    </span>
                </div>

                {{-- File Title --}}
                <div>
                    <h2 class="font-extrabold text-xl text-white truncate" title="{{ $fileInfo['filename'] }}">
                        {{ $fileInfo['filename'] }}
                    </h2>
                </div>

                {{-- Media Preview Display Area --}}
                <div class="rounded-2xl border border-zinc-800 bg-zinc-950 overflow-hidden flex items-center justify-center min-h-[250px] max-h-[400px] shadow-inner relative group">
                    @if($fileInfo['category'] === 'image')
                        <img src="{{ $fileInfo['url'] }}" alt="{{ $fileInfo['filename'] }}" class="max-h-[400px] w-auto object-contain" />
                    @elseif($fileInfo['ext'] === 'pdf')
                        <iframe src="{{ $fileInfo['url'] }}" class="w-full h-[350px] border-0"></iframe>
                    @else
                        <div class="flex flex-col items-center gap-3 p-8 text-center text-zinc-400">
                            <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-zinc-900 text-brand border border-zinc-800">
                                <flux:icon icon="document-text" class="size-8" />
                            </div>
                            <div class="text-xs text-zinc-500">{{ __('Live preview is not available for this file type.') }}</div>
                        </div>
                    @endif
                </div>

                {{-- Action Download Button --}}
                <flux:button variant="primary" wire:click="download" icon="arrow-down-tray" class="w-full bg-gradient-to-r from-brand to-indigo-600 hover:from-brand-hover hover:to-indigo-500 text-white font-black py-4 text-sm rounded-2xl shadow-xl shadow-brand/25 transition-all cursor-pointer">
                    {{ __('Download File') }}
                </flux:button>
            </div>
        @endif
    </main>

    {{-- Bottom Footer --}}
    <footer class="w-full max-w-xl mx-auto text-center text-[11px] text-zinc-500 py-4">
        &copy; {{ date('Y') }} {{ config('app.name', 'Laravel-adminX') }} — {{ __('Secure Enterprise Media Sharing') }}
    </footer>
</div>
