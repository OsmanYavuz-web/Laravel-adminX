<?php

namespace App\Modules\ExcaCoin\Livewire\Pages\Finds;

use App\Modules\ExcaCoin\Models\ExcavationProject;
use App\Modules\ExcaCoin\Models\Find;
use Illuminate\Support\Carbon;
use Livewire\Component;
use Livewire\WithFileUploads;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Edit extends Component
{
    use WithFileUploads;

    public ExcavationProject $project;

    public Find $find;

    // --- Zorunlu Alanlar ---
    public string $findDate = '';

    public string $inventoryNumber = '';

    public string $excavationArea = '';

    // --- Bağlam Bilgileri ---
    public string $excavationSeason = '';

    public string $sector = '';

    public string $area = '';

    public string $trench = '';

    public string $square = '';

    public string $subSquare = '';

    public string $locus = '';

    public string $context = '';

    public string $stratigraphicUnit = '';

    public string $unit = '';

    public string $layer = '';

    public string $level = '';

    public string $phase = '';

    public string $feature = '';

    public string $graveNumber = '';

    public string $structure = '';

    public string $room = '';

    public string $architecturalFeature = '';

    // --- Konum ---
    public string $findSpot = '';

    public string $elevation = '';

    public string $coordinateX = '';

    public string $coordinateY = '';

    public string $coordinateZ = '';

    // --- Numaralandırma & Not ---
    public string $findNumber = '';

    public string $bagNumber = '';

    public string $findGroup = '';

    public string $findNote = '';

    // --- Medya (yeni yüklemeler) ---
    public $newCoverPhoto = null;

    public array $newGallery = [];

    public array $newDocuments = [];

    // --- Silinecek medya ID'leri ---
    public array $deleteMediaIds = [];

    public function mount(): void
    {
        $f = $this->find;
        $this->findDate = $f->find_date ? Carbon::parse($f->find_date)->format('Y-m-d') : '';
        $this->inventoryNumber = $f->inventory_number;
        $this->excavationArea = $f->excavation_area;
        $this->excavationSeason = $f->excavation_season ?? '';
        $this->sector = $f->sector ?? '';
        $this->area = $f->area ?? '';
        $this->trench = $f->trench ?? '';
        $this->square = $f->square ?? '';
        $this->subSquare = $f->sub_square ?? '';
        $this->locus = $f->locus ?? '';
        $this->context = $f->context ?? '';
        $this->stratigraphicUnit = $f->stratigraphic_unit ?? '';
        $this->unit = $f->unit ?? '';
        $this->layer = $f->layer ?? '';
        $this->level = $f->level ?? '';
        $this->phase = $f->phase ?? '';
        $this->feature = $f->feature ?? '';
        $this->graveNumber = $f->grave_number ?? '';
        $this->structure = $f->structure ?? '';
        $this->room = $f->room ?? '';
        $this->architecturalFeature = $f->architectural_feature ?? '';
        $this->findSpot = $f->find_spot ?? '';
        $this->elevation = $f->elevation ? (string) $f->elevation : '';
        $this->coordinateX = $f->coordinate_x ? (string) $f->coordinate_x : '';
        $this->coordinateY = $f->coordinate_y ? (string) $f->coordinate_y : '';
        $this->coordinateZ = $f->coordinate_z ? (string) $f->coordinate_z : '';
        $this->findNumber = $f->find_number ?? '';
        $this->bagNumber = $f->bag_number ?? '';
        $this->findGroup = $f->find_group ?? '';
        $this->findNote = $f->find_note ?? '';
    }

    protected function rules(): array
    {
        return [
            'findDate' => 'required|date',
            'inventoryNumber' => 'required|string|max:100|unique:finds,inventory_number,'.$this->find->id.',id,excavation_project_id,'.$this->project->id,
            'excavationArea' => 'required|string|max:200',
            'excavationSeason' => 'nullable|string|max:50',
            'sector' => 'nullable|string|max:100',
            'area' => 'nullable|string|max:100',
            'trench' => 'nullable|string|max:100',
            'square' => 'nullable|string|max:100',
            'subSquare' => 'nullable|string|max:100',
            'locus' => 'nullable|string|max:100',
            'context' => 'nullable|string|max:100',
            'stratigraphicUnit' => 'nullable|string|max:100',
            'unit' => 'nullable|string|max:100',
            'layer' => 'nullable|string|max:100',
            'level' => 'nullable|string|max:100',
            'phase' => 'nullable|string|max:100',
            'feature' => 'nullable|string|max:200',
            'graveNumber' => 'nullable|string|max:50',
            'structure' => 'nullable|string|max:200',
            'room' => 'nullable|string|max:100',
            'architecturalFeature' => 'nullable|string|max:200',
            'findSpot' => 'nullable|string|max:200',
            'elevation' => 'nullable|numeric',
            'coordinateX' => 'nullable|numeric',
            'coordinateY' => 'nullable|numeric',
            'coordinateZ' => 'nullable|numeric',
            'findNumber' => 'nullable|string|max:100',
            'bagNumber' => 'nullable|string|max:100',
            'findGroup' => 'nullable|string|max:100',
            'findNote' => 'nullable|string|max:2000',
            'newCoverPhoto' => 'nullable|image|max:10240',
            'newGallery.*' => 'nullable|image|max:10240',
            'newDocuments.*' => 'nullable|file|mimes:pdf,svg,png,jpg,jpeg|max:20480',
        ];
    }

    public function removeMedia(int $mediaId): void
    {
        $media = Media::findOrFail($mediaId);
        if ($media->model_id === $this->find->id) {
            $media->delete();
            $this->find->refresh();
        }
    }

    public function save(): void
    {
        $this->validate();

        $this->find->update([
            'find_date' => $this->findDate,
            'inventory_number' => $this->inventoryNumber,
            'excavation_area' => $this->excavationArea,
            'excavation_season' => $this->excavationSeason ?: null,
            'sector' => $this->sector ?: null,
            'area' => $this->area ?: null,
            'trench' => $this->trench ?: null,
            'square' => $this->square ?: null,
            'sub_square' => $this->subSquare ?: null,
            'locus' => $this->locus ?: null,
            'context' => $this->context ?: null,
            'stratigraphic_unit' => $this->stratigraphicUnit ?: null,
            'unit' => $this->unit ?: null,
            'layer' => $this->layer ?: null,
            'level' => $this->level ?: null,
            'phase' => $this->phase ?: null,
            'feature' => $this->feature ?: null,
            'grave_number' => $this->graveNumber ?: null,
            'structure' => $this->structure ?: null,
            'room' => $this->room ?: null,
            'architectural_feature' => $this->architecturalFeature ?: null,
            'find_spot' => $this->findSpot ?: null,
            'elevation' => $this->elevation ?: null,
            'coordinate_x' => $this->coordinateX ?: null,
            'coordinate_y' => $this->coordinateY ?: null,
            'coordinate_z' => $this->coordinateZ ?: null,
            'find_number' => $this->findNumber ?: null,
            'bag_number' => $this->bagNumber ?: null,
            'find_group' => $this->findGroup ?: null,
            'find_note' => $this->findNote ?: null,
        ]);

        if ($this->newCoverPhoto) {
            $this->find->clearMediaCollection('cover');
            $this->find->addMedia($this->newCoverPhoto)->toMediaCollection('cover');
        }
        if (is_array($this->newGallery)) {
            foreach ($this->newGallery as $file) {
                if ($file) {
                    $this->find->addMedia($file)->toMediaCollection('gallery');
                }
            }
        }
        if (is_array($this->newDocuments)) {
            foreach ($this->newDocuments as $file) {
                if ($file) {
                    $this->find->addMedia($file)->toMediaCollection('document');
                }
            }
        }

        $this->dispatch('toast', message: __('Buluntu güncellendi.'), type: 'success');
        $this->redirectRoute('finds.index', $this->project, navigate: true);
    }

    public function render()
    {
        return view('exca-coin::pages.finds.edit')
            ->layout('layouts.app', ['title' => __('Buluntu Düzenle').' — '.$this->find->inventory_number]);
    }
}
