<?php

use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Livewire\Component;
use App\Models\Role;
use App\Models\Permission;
use App\Models\Language;
use Flux\Flux;
use Illuminate\Support\Str;

new #[Title('Roles & Permissions')] #[Layout('layouts.app')] class extends Component {
    public array $displayName = [];
    public array $selectedPermissions = [];
    public ?int $editingRoleId = null;
    public bool $showRoleModal = false;

    public function mount(): void
    {
        abort_unless(auth()->user()->can('roles.view'), 403);
    }

    public function createRole(): void
    {
        abort_unless(auth()->user()->can('roles.create'), 403);
        $this->reset(['displayName', 'selectedPermissions', 'editingRoleId']);
        $languages = Language::getActive();
        foreach ($languages as $lang) {
            $this->displayName[$lang['code']] = '';
        }
        $this->showRoleModal = true;
    }

    public function editRole(int $id): void
    {
        abort_unless(auth()->user()->can('roles.update'), 403);
        $role = Role::findOrFail($id);
        $this->editingRoleId = $role->id;
        $this->selectedPermissions = $role->permissions->pluck('name')->toArray();
        
        $languages = Language::getActive();
        $this->displayName = [];
        foreach ($languages as $lang) {
            $this->displayName[$lang['code']] = $role->getTranslation('display_name', $lang['code'], false) ?: '';
        }
        
        $this->showRoleModal = true;
    }

    public function saveRole(): void
    {
        abort_unless(auth()->user()->can($this->editingRoleId ? 'roles.update' : 'roles.create'), 403);

        $defaultLang = Language::getDefault()['code'] ?? 'tr';
        
        $this->validate([
            "displayName.{$defaultLang}" => ['required', 'string', 'max:255'],
        ]);

        if ($this->editingRoleId) {
            $role = Role::findOrFail($this->editingRoleId);
        } else {
            $primaryName = $this->displayName['en'] ?? $this->displayName['tr'] ?? reset($this->displayName);
            $slugName = Str::slug($primaryName);
            
            // Ensure unique slug name
            $count = Role::where('name', 'like', "{$slugName}%")->count();
            if ($count > 0) {
                $slugName .= '-' . ($count + 1);
            }

            $role = Role::create(['name' => $slugName, 'guard_name' => 'web']);
        }

        $role->setTranslations('display_name', array_filter($this->displayName));
        $role->save();

        $role->syncPermissions($this->selectedPermissions);

        $this->showRoleModal = false;
        Flux::toast(variant: 'success', text: __('Role saved successfully.'));
    }

    public function deleteRole(int $id): void
    {
        abort_unless(auth()->user()->can('roles.delete'), 403);

        $role = Role::findOrFail($id);
        if ($role->name === 'super-admin' || $role->name === 'Super Admin') {
            Flux::toast(variant: 'danger', text: __('Super Admin role cannot be deleted.'));
            return;
        }
        $role->delete();
        Flux::toast(variant: 'success', text: __('Role deleted successfully.'));
    }

    public function with(): array
    {
        $permissions = Permission::all();

        // Group permissions by their prefix (e.g. users, finds, coins...).
        // Group titles, icons and colors come from the module manifests.
        $groupedPermissions = [];
        foreach ($permissions as $perm) {
            $parts = explode('.', $perm->name);
            $group = $parts[0] ?? 'general';
            $groupedPermissions[$group][] = $perm;
        }

        return [
            'roles' => Role::with('permissions')->get(),
            'groupedPermissions' => $groupedPermissions,
            'languages' => Language::getActive(),
            'groupMeta' => app(\App\Support\Modules\ModuleManager::class)->permissionGroups(),
        ];
    }
}; ?>
<div>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="xl" class="font-extrabold tracking-tight">{{ __('Roles & Permissions') }}</flux:heading>
                <flux:subheading>{{ __('Manage user roles and authorization permissions.') }}</flux:subheading>
            </div>
            @can('roles.create')
                <flux:button variant="primary" icon="plus" wire:click="createRole" class="bg-brand hover:bg-brand-hover text-white shadow-xs cursor-pointer px-4 py-2 text-sm">
                    {{ __('New Role') }}
                </flux:button>
            @endcan
        </div>

        {{-- Roles Cards Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach($roles as $role)
                <div class="rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-6 space-y-5 shadow-xs hover:border-brand/30 transition-all group">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-brand/15 text-brand dark:text-brand-accent shadow-2xs">
                                <flux:icon icon="shield-check" class="size-5" />
                            </span>
                            <div>
                                <h3 class="font-bold text-base text-zinc-900 dark:text-white">
                                    {{ $role->getTranslation('display_name', app()->getLocale(), false) ?: $role->name }}
                                </h3>
                                <div class="text-[11px] font-mono text-zinc-400">{{ $role->name }}</div>
                            </div>
                        </div>
                        <div class="flex items-center gap-1">
                            @can('roles.update')
                                <flux:button variant="ghost" icon="pencil-square" size="sm" wire:click="editRole({{ $role->id }})" class="hover:bg-brand/10 hover:text-brand transition-colors cursor-pointer" />
                            @endcan
                            @can('roles.delete')
                                @if(!in_array($role->name, ['super-admin', 'Super Admin']))
                                    <flux:button variant="ghost" icon="trash" size="sm" class="text-red-500 hover:bg-red-500/10 hover:text-red-600 transition-colors cursor-pointer" wire:click="deleteRole({{ $role->id }})" wire:confirm="{{ __('Are you sure you want to delete this role?') }}" />
                                @endif
                            @endcan
                        </div>
                    </div>

                    @php $permCount = $role->permissions->count(); @endphp
                    <div x-data="{ expanded: false }" class="space-y-2">
                        <button @click="expanded = !expanded" class="w-full flex items-center justify-between text-xs font-semibold text-zinc-400 uppercase tracking-wider hover:text-zinc-600 dark:hover:text-zinc-300 transition-colors cursor-pointer" type="button">
                            <span>{{ __('Permissions') }} ({{ $permCount }}):</span>
                            <flux:icon icon="chevron-down" class="size-4 transition-transform duration-200" :class="expanded && 'rotate-180'" />
                        </button>
                        <div x-show="!expanded" class="flex flex-wrap gap-1.5 pt-1">
                            @foreach($role->permissions->take(5) as $perm)
                                <flux:badge size="sm" variant="subtle" class="bg-zinc-100 dark:bg-zinc-800/80 border border-zinc-200/80 dark:border-zinc-700 text-zinc-800 dark:text-zinc-200 font-medium text-xs flex items-center gap-1">
                                    <flux:icon icon="key" class="size-3 text-brand" />
                                    <span>{{ $perm->getTranslation('display_name', app()->getLocale(), false) ?: $perm->name }}</span>
                                </flux:badge>
                            @endforeach
                            @if($permCount > 5)
                                <button @click="expanded = true" class="text-xs text-brand hover:text-brand-hover font-semibold cursor-pointer" type="button">+{{ $permCount - 5 }} {{ __('more') }}</button>
                            @endif
                        </div>
                        <div x-show="expanded" x-cloak class="flex flex-wrap gap-1.5 pt-1">
                            @foreach($role->permissions as $perm)
                                <flux:badge size="sm" variant="subtle" class="bg-zinc-100 dark:bg-zinc-800/80 border border-zinc-200/80 dark:border-zinc-700 text-zinc-800 dark:text-zinc-200 font-medium text-xs flex items-center gap-1">
                                    <flux:icon icon="key" class="size-3 text-brand" />
                                    <span>{{ $perm->getTranslation('display_name', app()->getLocale(), false) ?: $perm->name }}</span>
                                </flux:badge>
                            @endforeach
                        </div>
                        @if($permCount > 5)
                            <button x-show="expanded" x-cloak @click="expanded = false" class="text-xs text-brand hover:text-brand-hover font-semibold cursor-pointer flex items-center gap-1 mt-1" type="button">
                                <flux:icon icon="chevron-up" class="size-3" /> {{ __('Show less') }}
                            </button>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Role Modal --}}
        <flux:modal wire:model="showRoleModal" class="max-w-5xl md:max-w-6xl w-full min-w-[850px] p-4">
            <form wire:submit="saveRole" class="space-y-6">
                <div class="flex items-start gap-4">
                    <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-brand/15 text-brand dark:text-brand-accent shadow-2xs border border-brand/20">
                        <flux:icon icon="shield-check" class="size-7" />
                    </div>
                    <div>
                        <flux:heading size="xl" class="font-extrabold text-zinc-900 dark:text-white text-xl">
                            {{ $editingRoleId ? __('Edit Role') : __('New Role') }}
                        </flux:heading>
                        <flux:subheading class="text-xs text-zinc-500 mt-0.5">
                            {{ __('Define role name and select associated permissions.') }}
                        </flux:subheading>
                    </div>
                </div>

                <div class="space-y-5 pt-2">
                    {{-- Translatable Role Name Input --}}
                    <x-translatable-input
                        name="displayName"
                        :label="__('Role Name')"
                        :languages="$languages"
                        :value="$displayName"
                        required
                        placeholder="e.g. Archaeologist / Arkeolog"
                    />

                    {{-- Grouped Permissions Matrix --}}
                    <div class="space-y-4 pt-2">
                        <flux:label class="font-bold text-sm text-zinc-800 dark:text-zinc-200">
                            {{ __('Permissions / Authorization Matrix') }}
                        </flux:label>

                        <div class="space-y-4 max-h-[520px] overflow-y-auto pr-2">
                            @php($navColors = nav_colors())
                            @foreach($groupedPermissions as $group => $perms)
                                <div class="space-y-2.5 rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-zinc-50/40 dark:bg-zinc-800/20 p-4">
                                    <div class="flex items-center gap-2 font-bold text-xs uppercase text-zinc-500 dark:text-zinc-400">
                                        <flux:icon :icon="$groupMeta[$group]['icon'] ?? 'folder'" class="size-4 {{ isset($groupMeta[$group]['color']) ? ($navColors['soft'][$groupMeta[$group]['color']] ?? 'text-brand') : 'text-brand' }}" />
                                        <span>{{ $groupMeta[$group]['title'] ?? __(Str::headline($group)) }}</span>
                                    </div>

                                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 pt-1">
                                        @foreach($perms as $perm)
                                            <label class="flex items-center justify-between p-3.5 rounded-xl border border-zinc-200 dark:border-zinc-700/70 bg-white dark:bg-zinc-800/60 hover:border-brand/40 dark:hover:border-brand/40 has-[:checked]:bg-brand/10 has-[:checked]:border-brand transition-all cursor-pointer group">
                                                <div class="flex items-center gap-2.5">
                                                    <input type="checkbox" wire:model="selectedPermissions" value="{{ $perm->name }}" class="rounded-md text-brand focus:ring-brand dark:bg-zinc-900 border-zinc-300 dark:border-zinc-700" />
                                                    <div>
                                                        <div class="text-xs font-bold text-zinc-800 dark:text-zinc-200 group-hover:text-brand dark:group-hover:text-brand-accent transition-colors">
                                                            {{ $perm->getTranslation('display_name', app()->getLocale(), false) ?: $perm->name }}
                                                        </div>
                                                        <div class="text-[10px] font-mono text-zinc-400">{{ $perm->name }}</div>
                                                    </div>
                                                </div>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-5 border-t border-zinc-100 dark:border-zinc-800">
                    <flux:button variant="filled" wire:click="$set('showRoleModal', false)" class="cursor-pointer px-5">{{ __('Cancel') }}</flux:button>
                    <flux:button variant="primary" type="submit" icon="check" class="cursor-pointer bg-brand hover:bg-brand-hover text-white shadow-xs px-6 py-2">{{ __('Save Role') }}</flux:button>
                </div>
            </form>
        </flux:modal>
    </div>
</div>
