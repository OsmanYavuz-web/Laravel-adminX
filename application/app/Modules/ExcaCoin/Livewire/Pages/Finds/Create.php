<?php

namespace App\Modules\ExcaCoin\Livewire\Pages\Finds;

use App\Modules\ExcaCoin\Models\ExcavationProject;
use App\Modules\ExcaCoin\Models\Find;
use Livewire\Component;
use Livewire\WithFileUploads;

class Create extends Component
{
    use WithFileUploads;

    public ?ExcavationProject $project = null;

    public ?int $projectId = null;

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

    // --- Medya ---
    public $coverPhoto = null;

    public array $gallery = [];

    public array $documents = [];

    protected function rules(): array
    {
        $projId = ($this->project && $this->project->exists) ? $this->project->id : $this->projectId;

        return [
            'projectId' => ($this->project && $this->project->exists) ? 'nullable' : 'required|exists:excavation_projects,id',
            'findDate' => 'required|date',
            'inventoryNumber' => 'required|string|max:100|unique:finds,inventory_number,NULL,id,excavation_project_id,'.($projId ?? 'NULL'),
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
            'coverPhoto' => 'nullable|image|max:10240',
            'gallery.*' => 'nullable|image|max:10240',
            'documents.*' => 'nullable|file|mimes:pdf,svg,png,jpg,jpeg|max:20480',
        ];
    }

    protected function messages(): array
    {
        return [
            'projectId.required' => __('Kazı projesi seçimi zorunludur.'),
            'findDate.required' => __('Buluntu tarihi zorunludur.'),
            'inventoryNumber.required' => __('Envanter numarası zorunludur.'),
            'inventoryNumber.unique' => __('Bu envanter numarası bu projede zaten kullanılmış.'),
            'excavationArea.required' => __('Kazı alanı zorunludur.'),
        ];
    }

    public function save(): void
    {
        $this->validate();

        $targetProjectId = ($this->project && $this->project->exists) ? $this->project->id : $this->projectId;

        $find = Find::create([
            'excavation_project_id' => $targetProjectId,
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
            'created_by' => auth()->id(),
        ]);

        // Medya yükleme
        if ($this->coverPhoto) {
            $find->addMedia($this->coverPhoto)->toMediaCollection('cover');
        }
        if (is_array($this->gallery)) {
            foreach ($this->gallery as $file) {
                if ($file) {
                    $find->addMedia($file)->toMediaCollection('gallery');
                }
            }
        }
        if (is_array($this->documents)) {
            foreach ($this->documents as $file) {
                if ($file) {
                    $find->addMedia($file)->toMediaCollection('document');
                }
            }
        }

        $this->dispatch('toast', message: __('Buluntu kaydedildi.'), type: 'success');
        $targetProject = ($this->project && $this->project->exists) ? $this->project : ExcavationProject::find($targetProjectId);
        $this->redirectRoute('finds.index', $targetProject, navigate: true);
    }

    public function render()
    {
        $title = __('Yeni Buluntu').($this->project && $this->project->exists ? ' — '.$this->project->name : '');

        return view('exca-coin::pages.finds.create')
            ->layout('layouts.app', ['title' => $title]);
    }
}
