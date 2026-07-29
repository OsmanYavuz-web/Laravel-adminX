<?php

use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Livewire\Component;
use App\Models\User;
use App\Models\Role;
use Flux\Flux;
use Illuminate\Support\Facades\Hash;

new #[Title('User Management')] #[Layout('layouts.app')] class extends Component {
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public array $selectedRoles = [];
    public ?int $editingUserId = null;
    public bool $showUserModal = false;

    public function mount(): void
    {
        abort_unless(auth()->user()->can('users.view'), 403);
    }

    public function createUser(): void
    {
        abort_unless(auth()->user()->can('users.create'), 403);
        $this->reset(['name', 'email', 'password', 'selectedRoles', 'editingUserId']);
        $this->showUserModal = true;
    }

    public function editUser(int $id): void
    {
        abort_unless(auth()->user()->can('users.update'), 403);
        $user = User::findOrFail($id);
        $this->editingUserId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->password = '';
        $this->selectedRoles = $user->roles->pluck('name')->toArray();
        $this->showUserModal = true;
    }

    public function saveUser(): void
    {
        abort_unless(auth()->user()->can($this->editingUserId ? 'users.update' : 'users.create'), 403);

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', \Illuminate\Validation\Rule::unique('users')->ignore($this->editingUserId)],
        ];

        if (!$this->editingUserId) {
            $rules['password'] = ['required', 'string', 'min:8'];
        }

        $this->validate($rules);

        if ($this->editingUserId) {
            $user = User::findOrFail($this->editingUserId);
            $user->name = $this->name;
            $user->email = $this->email;
            if (!empty($this->password)) {
                $user->password = Hash::make($this->password);
            }
            $user->save();
        } else {
            $user = User::create([
                'name' => $this->name,
                'email' => $this->email,
                'password' => Hash::make($this->password),
                'locale' => 'tr',
            ]);
        }

        $user->syncRoles($this->selectedRoles);

        $this->showUserModal = false;
        Flux::toast(variant: 'success', text: __('User saved successfully.'));
    }

    public function deleteUser(int $id): void
    {
        abort_unless(auth()->user()->can('users.delete'), 403);

        if ($id === auth()->id()) {
            Flux::toast(variant: 'danger', text: __('You cannot delete your own account from here.'));
            return;
        }

        $user = User::findOrFail($id);
        $user->delete();
        Flux::toast(variant: 'success', text: __('User deleted successfully.'));
    }

    public function with(): array
    {
        return [
            'users' => User::with('roles')->latest()->get(),
            'allRoles' => Role::all(),
        ];
    }
}; ?>
<div>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="xl" class="font-extrabold tracking-tight">{{ __('User Management') }}</flux:heading>
                <flux:subheading>{{ __('Manage registered system users and assign role permissions.') }}</flux:subheading>
            </div>
            @can('users.create')
                <flux:button variant="primary" icon="plus" wire:click="createUser" class="bg-brand hover:bg-brand-hover text-white shadow-xs cursor-pointer px-4 py-2 text-sm">
                    {{ __('New User') }}
                </flux:button>
            @endcan
        </div>

        <div class="rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 overflow-hidden shadow-xs">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-zinc-600 dark:text-zinc-400">
                    <thead class="bg-zinc-50 dark:bg-zinc-800/60 text-xs font-bold uppercase text-zinc-500 border-b border-zinc-200 dark:border-zinc-800">
                        <tr>
                            <th class="px-6 py-4">{{ __('User') }}</th>
                            <th class="px-6 py-4">{{ __('Roles') }}</th>
                            <th class="px-6 py-4">{{ __('Preferred Language') }}</th>
                            <th class="px-6 py-4 text-right">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                        @foreach($users as $u)
                            <tr class="hover:bg-zinc-50/60 dark:hover:bg-zinc-800/40 transition-colors">
                                <td class="px-6 py-4 flex items-center gap-3.5">
                                    <flux:avatar :name="$u->name" :initials="$u->initials()" size="sm" class="ring-2 ring-brand/20" />
                                    <div>
                                        <div class="font-semibold text-zinc-900 dark:text-white">{{ $u->name }}</div>
                                        <div class="text-xs text-zinc-500 font-mono">{{ $u->email }}</div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-wrap gap-1.5">
                                        @forelse($u->roles as $role)
                                            <flux:badge size="sm" variant="solid" class="bg-brand/15 text-brand dark:text-brand-accent border border-brand/20 font-semibold">
                                                {{ $role->getTranslation('display_name', app()->getLocale(), false) ?: $role->name }}
                                            </flux:badge>
                                        @empty
                                            <flux:badge size="sm" variant="subtle" class="text-zinc-400">{{ __('No Role') }}</flux:badge>
                                        @endforelse
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-xs font-bold uppercase text-zinc-500">
                                    <span class="px-2.5 py-1 rounded-md bg-zinc-100 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700">{{ strtoupper($u->locale ?? 'tr') }}</span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        @can('users.update')
                                            <flux:button variant="ghost" icon="pencil-square" size="sm" wire:click="editUser({{ $u->id }})" class="hover:bg-brand/10 hover:text-brand transition-colors cursor-pointer" />
                                        @endcan
                                        @can('users.delete')
                                            @if($u->id !== auth()->id())
                                                <flux:button variant="ghost" icon="trash" size="sm" class="text-red-500 hover:bg-red-500/10 hover:text-red-600 transition-colors cursor-pointer" wire:click="deleteUser({{ $u->id }})" wire:confirm="{{ __('Are you sure you want to delete this user?') }}" />
                                            @endif
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <flux:modal wire:model="showUserModal" class="max-w-2xl min-w-[550px] p-3">
            <form wire:submit="saveUser" class="space-y-6">
                <div class="flex items-start gap-4">
                    <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-brand/15 text-brand dark:text-brand-accent shadow-2xs border border-brand/20">
                        <flux:icon icon="user-plus" class="size-7" />
                    </div>
                    <div>
                        <flux:heading size="xl" class="font-extrabold text-zinc-900 dark:text-white text-xl">{{ $editingUserId ? __('Edit User') : __('New User') }}</flux:heading>
                        <flux:subheading class="text-xs text-zinc-500 mt-0.5">{{ __('Fill user details and select system roles.') }}</flux:subheading>
                    </div>
                </div>

                <div class="space-y-5 pt-2">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <flux:input wire:model="name" :label="__('Name')" icon="user" placeholder="e.g. Ahmet Yılmaz" required />
                        <flux:input wire:model="email" :label="__('Email address')" icon="envelope" type="email" :placeholder="__('email@example.com')" required />
                    </div>
                    <flux:input wire:model="password" :label="__('Password')" icon="key" type="password" viewable :placeholder="$editingUserId ? __('Leave empty to keep current password') : ''" />

                    <div class="space-y-3 pt-2">
                        <flux:label class="font-bold text-sm text-zinc-800 dark:text-zinc-200">{{ __('System Roles') }}</flux:label>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                            @foreach($allRoles as $role)
                                <label class="flex items-center justify-between p-3.5 rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-zinc-50/50 dark:bg-zinc-800/40 hover:bg-brand/5 hover:border-brand/40 dark:hover:border-brand/40 has-[:checked]:bg-brand/10 has-[:checked]:border-brand transition-all cursor-pointer group">
                                    <div class="flex items-center gap-3">
                                        <input type="checkbox" wire:model="selectedRoles" value="{{ $role->name }}" class="rounded-md text-brand focus:ring-brand dark:bg-zinc-900 border-zinc-300 dark:border-zinc-700" />
                                        <span class="text-xs font-bold text-zinc-800 dark:text-zinc-200 group-hover:text-brand dark:group-hover:text-brand-accent transition-colors">
                                            {{ $role->getTranslation('display_name', app()->getLocale(), false) ?: $role->name }}
                                        </span>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-5 border-t border-zinc-100 dark:border-zinc-800">
                    <flux:button variant="filled" wire:click="$set('showUserModal', false)" class="cursor-pointer px-5">{{ __('Cancel') }}</flux:button>
                    <flux:button variant="primary" type="submit" icon="check" class="cursor-pointer bg-brand hover:bg-brand-hover text-white shadow-xs px-6 py-2">{{ __('Save User') }}</flux:button>
                </div>
            </form>
        </flux:modal>
    </div>
</div>
