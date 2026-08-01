<?php

namespace App\Livewire\Pages\Finds;

use App\Models\ExcavationProject;
use App\Models\Find;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public ?ExcavationProject $project = null;

    #[Url(as: 'q')]
    public string $search = '';
    public string $filterSeason = '';
    public string $filterArea   = '';

    public bool $showDeleteModal = false;
    public ?int $deletingId = null;

    public function mount(): void
    {
        abort_unless(auth()->user()->can('finds.view') || auth()->user()->hasRole('super-admin'), 403);
    }

    #[Computed]
    public function finds()
    {
        return Find::query()
            ->whereHas('project', fn ($q) => $q->accessibleBy())
            ->when($this->project && $this->project->exists, fn ($q) => $q->where('excavation_project_id', $this->project->id))
            ->with(['project', 'media'])
            ->withCount('coins')
            ->when($this->search, fn ($q) => $q->where(function ($q) {
                $q->where('inventory_number', 'like', "%{$this->search}%")
                  ->orWhere('excavation_area', 'like', "%{$this->search}%")
                  ->orWhere('find_note', 'like', "%{$this->search}%");
            }))
            ->when($this->filterSeason, fn ($q) => $q->where('excavation_season', $this->filterSeason))
            ->when($this->filterArea, fn ($q) => $q->where('excavation_area', 'like', "%{$this->filterArea}%"))
            ->latest('find_date')
            ->paginate(20);
    }

    #[Computed]
    public function seasons(): array
    {
        return Find::query()
            ->when($this->project && $this->project->exists, fn ($q) => $q->where('excavation_project_id', $this->project->id))
            ->whereNotNull('excavation_season')
            ->distinct()
            ->orderBy('excavation_season')
            ->pluck('excavation_season')
            ->toArray();
    }

    public function updatedSearch(): void { $this->resetPage(); }
    public function updatedFilterSeason(): void { $this->resetPage(); }
    public function updatedFilterArea(): void { $this->resetPage(); }

    public function confirmDelete(int $id): void
    {
        $this->deletingId = $id;
        $this->showDeleteModal = true;
    }

    public function delete(): void
    {
        if (! $this->deletingId) return;

        $find = Find::withCount('coins')->findOrFail($this->deletingId);

        if ($find->coins_count > 0) {
            $this->dispatch('toast', message: __('Bu buluntuda sikke kaydı bulunduğu için silinemez.'), type: 'danger');
            $this->showDeleteModal = false;
            $this->deletingId = null;
            return;
        }

        $find->clearMediaCollection('cover');
        $find->clearMediaCollection('gallery');
        $find->clearMediaCollection('document');
        $find->delete();

        $this->showDeleteModal = false;
        $this->dispatch('toast', message: __('Buluntu silindi.'), type: 'success');
        $this->deletingId = null;
    }

    public function render()
    {
        $title = __('Buluntular') . ($this->project && $this->project->exists ? ' — ' . $this->project->name : '');
        return view('pages.finds.index')
            ->layout('layouts.app', ['title' => $title]);
    }
}
