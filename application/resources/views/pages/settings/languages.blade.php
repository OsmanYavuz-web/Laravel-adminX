<?php

use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Livewire\Component;
use App\Models\Language;
use Flux\Flux;

new #[Title('Language Management')] #[Layout('layouts.app')] class extends Component {
    public string $code = '';
    public string $langName = '';
    public string $nativeName = '';
    public string $flag = '';
    public bool $isActive = true;
    public bool $isDefault = false;
    public int $sortOrder = 0;
    public ?int $editingId = null;
    public bool $showModal = false;

    public function mount(): void
    {
        abort_unless(auth()->user()->can('languages.view'), 403);
    }

    public function createLanguage(): void
    {
        abort_unless(auth()->user()->can('languages.create'), 403);
        $this->reset(['code', 'langName', 'nativeName', 'flag', 'isActive', 'isDefault', 'sortOrder', 'editingId']);
        $this->sortOrder = Language::max('sort_order') + 1;
        $this->showModal = true;
    }

    public function editLanguage(int $id): void
    {
        abort_unless(auth()->user()->can('languages.update'), 403);
        $lang = Language::findOrFail($id);
        $this->editingId = $lang->id;
        $this->code = $lang->code;
        $this->langName = $lang->name;
        $this->nativeName = $lang->native_name;
        $this->flag = $lang->flag;
        $this->isActive = $lang->is_active;
        $this->isDefault = $lang->is_default;
        $this->sortOrder = $lang->sort_order;
        $this->showModal = true;
    }

    public function saveLanguage(): void
    {
        abort_unless(auth()->user()->can($this->editingId ? 'languages.update' : 'languages.create'), 403);

        $this->validate([
            'code' => ['required', 'string', 'max:5', \Illuminate\Validation\Rule::unique('languages', 'code')->ignore($this->editingId)],
            'langName' => ['required', 'string', 'max:255'],
            'nativeName' => ['required', 'string', 'max:255'],
            'flag' => ['required', 'string', 'max:10'],
            'sortOrder' => ['required', 'integer', 'min:0'],
        ]);

        // If setting as default, unset previous default
        if ($this->isDefault) {
            Language::where('is_default', true)->update(['is_default' => false]);
        }

        if ($this->editingId) {
            $lang = Language::findOrFail($this->editingId);
            $lang->update([
                'code' => $this->code,
                'name' => $this->langName,
                'native_name' => $this->nativeName,
                'flag' => $this->flag,
                'is_active' => $this->isActive,
                'is_default' => $this->isDefault,
                'sort_order' => $this->sortOrder,
            ]);
        } else {
            Language::create([
                'code' => $this->code,
                'name' => $this->langName,
                'native_name' => $this->nativeName,
                'flag' => $this->flag,
                'is_active' => $this->isActive,
                'is_default' => $this->isDefault,
                'sort_order' => $this->sortOrder,
            ]);
        }

        Language::clearCache();
        $this->showModal = false;
        Flux::toast(variant: 'success', text: __('Language saved successfully.'));
    }

    public function toggleActive(int $id): void
    {
        abort_unless(auth()->user()->can('languages.update'), 403);
        $lang = Language::findOrFail($id);
        if ($lang->is_default && $lang->is_active) {
            Flux::toast(variant: 'danger', text: __('Default language cannot be deactivated.'));
            return;
        }
        $lang->update(['is_active' => !$lang->is_active]);
        Language::clearCache();
    }

    public function setDefault(int $id): void
    {
        abort_unless(auth()->user()->can('languages.update'), 403);
        Language::where('is_default', true)->update(['is_default' => false]);
        $lang = Language::findOrFail($id);
        $lang->update(['is_default' => true, 'is_active' => true]);
        Language::clearCache();
        Flux::toast(variant: 'success', text: __('Default language updated.'));
    }

    public function deleteLanguage(int $id): void
    {
        abort_unless(auth()->user()->can('languages.delete'), 403);
        $lang = Language::findOrFail($id);
        if ($lang->is_default) {
            Flux::toast(variant: 'danger', text: __('Default language cannot be deleted.'));
            return;
        }
        $lang->delete();
        Language::clearCache();
        Flux::toast(variant: 'success', text: __('Language deleted successfully.'));
    }

    public function with(): array
    {
        return [
            'languages' => Language::orderBy('sort_order')->get(),
        ];
    }
}; ?>
<div>
    <div class="space-y-6">
        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="xl" class="font-extrabold tracking-tight">{{ __('Languages') }}</flux:heading>
                <flux:subheading>{{ __('Manage content languages for multi-language support.') }}</flux:subheading>
            </div>
            <flux:button variant="primary" icon="plus" wire:click="createLanguage" class="bg-brand hover:bg-brand-hover text-white shadow-xs cursor-pointer px-4 py-2 text-sm">
                {{ __('New Language') }}
            </flux:button>
        </div>

        {{-- Languages Table --}}
        <div class="rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 overflow-hidden shadow-xs">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-zinc-600 dark:text-zinc-400">
                    <thead class="bg-zinc-50 dark:bg-zinc-800/60 text-xs font-bold uppercase text-zinc-500 border-b border-zinc-200 dark:border-zinc-800">
                        <tr>
                            <th class="px-6 py-4 w-16">{{ __('Order') }}</th>
                            <th class="px-6 py-4">{{ __('Language') }}</th>
                            <th class="px-6 py-4">{{ __('Code') }}</th>
                            <th class="px-6 py-4 text-center">{{ __('Default') }}</th>
                            <th class="px-6 py-4 text-center">{{ __('Status') }}</th>
                            <th class="px-6 py-4 text-right">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                        @foreach($languages as $lang)
                            <tr class="hover:bg-zinc-50/60 dark:hover:bg-zinc-800/40 transition-colors">
                                <td class="px-6 py-4 text-xs font-bold text-zinc-400">{{ $lang->sort_order }}</td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <span class="text-2xl">{{ $lang->flag }}</span>
                                        <div>
                                            <div class="font-semibold text-zinc-900 dark:text-white">{{ $lang->native_name }}</div>
                                            <div class="text-xs text-zinc-500">{{ $lang->name }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2.5 py-1 rounded-md bg-zinc-100 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-xs font-bold uppercase">{{ $lang->code }}</span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @if($lang->is_default)
                                        <flux:badge size="sm" variant="solid" class="bg-brand/15 text-brand dark:text-brand-accent border border-brand/20 font-bold">{{ __('Default') }}</flux:badge>
                                    @else
                                        <button wire:click="setDefault({{ $lang->id }})" class="text-xs text-zinc-400 hover:text-brand cursor-pointer transition-colors">
                                            {{ __('Set Default') }}
                                        </button>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <flux:switch wire:click="toggleActive({{ $lang->id }})" :checked="$lang->is_active" />
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <flux:button variant="ghost" icon="pencil-square" size="sm" wire:click="editLanguage({{ $lang->id }})" class="hover:bg-brand/10 hover:text-brand transition-colors cursor-pointer" />
                                        @if(!$lang->is_default)
                                            <flux:button variant="ghost" icon="trash" size="sm" class="text-red-500 hover:bg-red-500/10 hover:text-red-600 transition-colors cursor-pointer" wire:click="deleteLanguage({{ $lang->id }})" wire:confirm="{{ __('Are you sure you want to delete this language?') }}" />
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Modal --}}
        <flux:modal wire:model="showModal" class="max-w-xl min-w-[500px] p-3">
            <form wire:submit="saveLanguage" class="space-y-6">
                <div class="flex items-start gap-4">
                    <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-brand/15 text-brand dark:text-brand-accent shadow-2xs border border-brand/20">
                        <flux:icon icon="language" class="size-7" />
                    </div>
                    <div>
                        <flux:heading size="xl" class="font-extrabold text-zinc-900 dark:text-white text-xl">
                            {{ $editingId ? __('Edit Language') : __('New Language') }}
                        </flux:heading>
                        <flux:subheading class="text-xs text-zinc-500 mt-0.5">
                            {{ __('Define language code, name, and flag emoji.') }}
                        </flux:subheading>
                    </div>
                </div>

                <div class="space-y-4 pt-2">
                    <div class="grid grid-cols-2 gap-4">
                        <flux:input wire:model="code" :label="__('Language Code')" icon="code-bracket" placeholder="e.g. tr, en, de" required maxlength="5" />
                        <flux:input wire:model="flag" :label="__('Flag Emoji')" placeholder="e.g. 🇹🇷" required maxlength="10" />
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <flux:input wire:model="langName" :label="__('Name (English)')" placeholder="e.g. Turkish" required />
                        <flux:input wire:model="nativeName" :label="__('Native Name')" placeholder="e.g. Türkçe" required />
                    </div>
                    <flux:input wire:model="sortOrder" :label="__('Sort Order')" type="number" min="0" required />

                    <div class="flex items-center gap-8 pt-2">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <flux:switch wire:model="isActive" />
                            <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('Active') }}</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <flux:switch wire:model="isDefault" />
                            <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('Default Language') }}</span>
                        </label>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-5 border-t border-zinc-100 dark:border-zinc-800">
                    <flux:button variant="filled" wire:click="$set('showModal', false)" class="cursor-pointer px-5">{{ __('Cancel') }}</flux:button>
                    <flux:button variant="primary" type="submit" icon="check" class="cursor-pointer bg-brand hover:bg-brand-hover text-white shadow-xs px-6 py-2">{{ __('Save Language') }}</flux:button>
                </div>
            </form>
        </flux:modal>
    </div>
</div>
