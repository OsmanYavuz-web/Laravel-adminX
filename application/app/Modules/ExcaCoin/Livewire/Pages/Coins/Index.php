<?php

namespace App\Modules\ExcaCoin\Livewire\Pages\Coins;

use App\Modules\ExcaCoin\Models\Coin;
use App\Modules\ExcaCoin\Models\Dictionary;
use App\Modules\ExcaCoin\Models\ExcavationProject;
use App\Modules\ExcaCoin\Models\Find;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public ?ExcavationProject $project = null;

    public ?Find $find = null;

    #[Url(as: 'q')]
    public string $search = '';

    public string $filterPeriod = '';

    public string $filterMetal = '';

    public string $filterMint = '';

    public string $viewMode = 'grid'; // grid | table

    public ?int $deletingId = null;

    public function mount(): void
    {
        abort_unless(auth()->user()->can('coins.view') || auth()->user()->hasRole('super-admin'), 403);
    }

    #[Computed]
    public function coins()
    {
        return Coin::query()
            ->whereHas('project', fn ($q) => $q->accessibleBy())
            ->when($this->find && $this->find->exists, fn ($q) => $q->where('find_id', $this->find->id))
            ->when($this->project && $this->project->exists, fn ($q) => $q->where('excavation_project_id', $this->project->id))
            ->with(['project', 'find', 'period', 'metal', 'mint', 'denomination'])
            ->when($this->search, fn ($q) => $q->where(function ($q) {
                $q->where('reference', 'like', "%{$this->search}%")
                    ->orWhere('note', 'like', "%{$this->search}%")
                    ->orWhere('obverse_legend', 'like', "%{$this->search}%")
                    ->orWhere('reverse_legend', 'like', "%{$this->search}%");
            }))
            ->when($this->filterPeriod, fn ($q) => $q->where('period_id', $this->filterPeriod))
            ->when($this->filterMetal, fn ($q) => $q->where('metal_id', $this->filterMetal))
            ->when($this->filterMint, fn ($q) => $q->where('mint_id', $this->filterMint))
            ->latest()
            ->paginate($this->viewMode === 'grid' ? 12 : 25);
    }

    #[Computed]
    public function periods(): Collection
    {
        return Dictionary::ofType('period')->get();
    }

    #[Computed]
    public function metals(): Collection
    {
        return Dictionary::ofType('metal')->get();
    }

    #[Computed]
    public function mints(): Collection
    {
        return Dictionary::ofType('mint')->get();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedFilterPeriod(): void
    {
        $this->resetPage();
    }

    public function updatedFilterMetal(): void
    {
        $this->resetPage();
    }

    public function updatedFilterMint(): void
    {
        $this->resetPage();
    }

    public function setViewMode(string $mode): void
    {
        $this->viewMode = $mode;
        $this->resetPage();
    }

    public bool $showDeleteModal = false;

    public function confirmDelete(int $id): void
    {
        $this->deletingId = $id;
        $this->showDeleteModal = true;
    }

    public function delete(): void
    {
        if (! $this->deletingId) {
            return;
        }
        $coin = Coin::findOrFail($this->deletingId);
        $coin->clearMediaCollection('obverse');
        $coin->clearMediaCollection('reverse');
        $coin->clearMediaCollection('gallery');
        $coin->clearMediaCollection('document');
        $coin->delete();
        $this->showDeleteModal = false;
        $this->dispatch('toast', message: __('Sikke silindi.'), type: 'success');
        $this->deletingId = null;
    }

    public function render()
    {
        $title = __('Sikkeler').($this->find && $this->find->exists ? ' — '.$this->find->inventory_number : '');

        return view('exca-coin::pages.coins.index')
            ->layout('layouts.app', ['title' => $title]);
    }
}
