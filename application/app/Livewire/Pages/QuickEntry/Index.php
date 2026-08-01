<?php

namespace App\Livewire\Pages\QuickEntry;

use App\Models\Coin;
use App\Models\Dictionary;
use App\Models\ExcavationProject;
use App\Models\Find;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;

class Index extends Component
{
    use WithFileUploads;

    // Proje seçimi
    public ?int $projectId = null;

    // --- BULUNTU ALANLARI ---
    public string $findDate         = '';
    public string $inventoryNumber   = '';
    public string $excavationArea    = '';
    public string $excavationSeason  = '';
    public string $sector            = '';
    public string $area              = '';
    public string $trench            = '';
    public string $square            = '';
    public string $subSquare         = '';
    public string $locus             = '';
    public string $context           = '';
    public string $stratigraphicUnit = '';
    public string $unit              = '';
    public string $layer             = '';
    public string $level             = '';
    public string $phase             = '';
    public string $feature           = '';
    public string $graveNumber       = '';
    public string $structure         = '';
    public string $room              = '';
    public string $architecturalFeature = '';
    public string $findSpot          = '';
    public string $elevation         = '';
    public string $coordinateX       = '';
    public string $coordinateY       = '';
    public string $coordinateZ       = '';
    public string $findNumber        = '';
    public string $bagNumber         = '';
    public string $findGroup         = '';
    public string $findNote          = '';
    public $coverPhoto               = null;
    public array $gallery            = [];
    public array $documents          = [];

    // Modal kontrolleri
    public bool $showResetModal = false;

    // --- SİKKE FORM KONTROLÜ ---
    public bool $addCoin = true;
    public string $coinActiveTab = 'identification';

    // --- SİKKE ALANLARI ---
    public ?int $periodId       = null;
    public ?int $authorityId    = null;
    public ?int $rulerId        = null;
    public ?int $regionId       = null;
    public ?int $mintId         = null;
    public ?int $metalId        = null;
    public ?int $denominationId = null;
    public string $dateRange    = '';
    public string $diameter     = '';
    public string $weight       = '';
    public string $axis         = '';
    public bool   $isCut        = false;
    public bool   $isPierced    = false;
    public string $obverseDescription    = '';
    public string $obverseLegend         = '';
    public string $obverseLegendExpanded = '';
    public string $reverseDescription    = '';
    public string $reverseLegend         = '';
    public string $reverseLegendExpanded = '';
    public string $mintMark     = '';
    public string $magistrate   = '';
    public string $controlMark  = '';
    public string $monogram     = '';
    public string $countermark  = '';
    public bool   $isOverstrike = false;
    public string $reference    = '';
    public string $note         = '';
    public $coinObversePhoto    = null;
    public $coinReversePhoto    = null;

    public function mount(): void
    {
        abort_unless(auth()->user()->can('quick_entry.access') || auth()->user()->hasRole('super-admin'), 403);
        $this->findDate = now()->format('Y-m-d');
        // İlk erişilebilir projeyi varsayılan yap
        $firstProject = ExcavationProject::accessibleBy()->first();
        if ($firstProject) {
            $this->projectId = $firstProject->id;
        }
    }

    #[Computed]
    public function accessibleProjects()
    {
        return ExcavationProject::accessibleBy()->where('is_active', true)->orderBy('name')->get();
    }

    #[Computed]
    public function selectedProject(): ?ExcavationProject
    {
        if (! $this->projectId) return null;
        return ExcavationProject::find($this->projectId);
    }

    // --- SÖZLÜKLER ---
    #[Computed] public function periods()       { return Dictionary::ofType('period')->get(); }
    #[Computed] public function authorities()   { return Dictionary::ofType('authority')->get(); }
    #[Computed] public function rulers()        { return Dictionary::ofType('ruler')->get(); }
    #[Computed] public function regions()       { return Dictionary::ofType('region')->get(); }
    #[Computed] public function mints()         { return Dictionary::ofType('mint')->get(); }
    #[Computed] public function metals()        { return Dictionary::ofType('metal')->get(); }
    #[Computed] public function denominations() { return Dictionary::ofType('denomination')->get(); }

    protected function rules(): array
    {
        $rules = [
            'projectId'       => 'required|exists:excavation_projects,id',
            'findDate'        => 'required|date',
            'inventoryNumber' => 'required|string|max:100|unique:finds,inventory_number,NULL,id,excavation_project_id,' . ($this->projectId ?? 'NULL'),
            'excavationArea'  => 'required|string|max:200',
            'excavationSeason'    => 'nullable|string|max:50',
            'sector'              => 'nullable|string|max:100',
            'area'                => 'nullable|string|max:100',
            'trench'              => 'nullable|string|max:100',
            'square'              => 'nullable|string|max:100',
            'subSquare'           => 'nullable|string|max:100',
            'locus'               => 'nullable|string|max:100',
            'context'             => 'nullable|string|max:100',
            'stratigraphicUnit'   => 'nullable|string|max:100',
            'unit'                => 'nullable|string|max:100',
            'layer'               => 'nullable|string|max:100',
            'level'               => 'nullable|string|max:100',
            'phase'               => 'nullable|string|max:100',
            'feature'             => 'nullable|string|max:200',
            'graveNumber'         => 'nullable|string|max:50',
            'structure'           => 'nullable|string|max:200',
            'room'                => 'nullable|string|max:100',
            'architecturalFeature' => 'nullable|string|max:200',
            'findSpot'    => 'nullable|string|max:200',
            'elevation'   => 'nullable|numeric',
            'coordinateX' => 'nullable|numeric',
            'coordinateY' => 'nullable|numeric',
            'coordinateZ' => 'nullable|numeric',
            'findNumber'  => 'nullable|string|max:100',
            'bagNumber'   => 'nullable|string|max:100',
            'findGroup'   => 'nullable|string|max:100',
            'findNote'    => 'nullable|string|max:2000',
            'coverPhoto'  => 'nullable|image|max:10240',
            'gallery.*'   => 'nullable|image|max:10240',
            'documents.*' => 'nullable|file|mimes:pdf,svg,png,jpg,jpeg|max:20480',
        ];

        if ($this->addCoin) {
            $rules = array_merge($rules, [
                'periodId'       => 'nullable|exists:dictionaries,id',
                'authorityId'    => 'nullable|exists:dictionaries,id',
                'rulerId'        => 'nullable|exists:dictionaries,id',
                'regionId'       => 'nullable|exists:dictionaries,id',
                'mintId'         => 'nullable|exists:dictionaries,id',
                'metalId'        => 'nullable|exists:dictionaries,id',
                'denominationId' => 'nullable|exists:dictionaries,id',
                'dateRange'      => 'nullable|string|max:100',
                'diameter'       => 'nullable|numeric',
                'weight'         => 'nullable|numeric',
                'axis'           => 'nullable|integer|between:1,12',
                'isCut'          => 'boolean',
                'isPierced'      => 'boolean',
                'obverseDescription'    => 'nullable|string|max:2000',
                'obverseLegend'         => 'nullable|string|max:200',
                'obverseLegendExpanded' => 'nullable|string|max:200',
                'reverseDescription'    => 'nullable|string|max:2000',
                'reverseLegend'         => 'nullable|string|max:200',
                'reverseLegendExpanded' => 'nullable|string|max:200',
                'mintMark'       => 'nullable|string|max:100',
                'magistrate'     => 'nullable|string|max:100',
                'controlMark'    => 'nullable|string|max:100',
                'monogram'       => 'nullable|string|max:100',
                'countermark'    => 'nullable|string|max:100',
                'isOverstrike'   => 'boolean',
                'reference'      => 'nullable|string|max:2000',
                'note'           => 'nullable|string|max:2000',
                'coinObversePhoto' => 'nullable|image|max:10240',
                'coinReversePhoto' => 'nullable|image|max:10240',
            ]);
        }

        return $rules;
    }

    protected function messages(): array
    {
        return [
            'projectId.required'       => __('Kazı projesi seçimi zorunludur.'),
            'findDate.required'        => __('Buluntu tarihi zorunludur.'),
            'inventoryNumber.required' => __('Envanter numarası zorunludur.'),
            'inventoryNumber.unique'   => __('Bu envanter numarası seçilen projede zaten mevcut.'),
            'excavationArea.required'  => __('Kazı alanı zorunludur.'),
        ];
    }

    public function save(): void
    {
        $this->validate();

        DB::transaction(function () {
            // 1. Buluntu Oluştur
            $find = Find::create([
                'excavation_project_id' => $this->projectId,
                'find_date'             => $this->findDate,
                'inventory_number'      => $this->inventoryNumber,
                'excavation_area'       => $this->excavationArea,
                'excavation_season'     => $this->excavationSeason ?: null,
                'sector'                => $this->sector ?: null,
                'area'                  => $this->area ?: null,
                'trench'                => $this->trench ?: null,
                'square'                => $this->square ?: null,
                'sub_square'            => $this->subSquare ?: null,
                'locus'                 => $this->locus ?: null,
                'context'               => $this->context ?: null,
                'stratigraphic_unit'    => $this->stratigraphicUnit ?: null,
                'unit'                  => $this->unit ?: null,
                'layer'                 => $this->layer ?: null,
                'level'                 => $this->level ?: null,
                'phase'                 => $this->phase ?: null,
                'feature'               => $this->feature ?: null,
                'grave_number'          => $this->graveNumber ?: null,
                'structure'             => $this->structure ?: null,
                'room'                  => $this->room ?: null,
                'architectural_feature' => $this->architecturalFeature ?: null,
                'find_spot'             => $this->findSpot ?: null,
                'elevation'             => $this->elevation ?: null,
                'coordinate_x'         => $this->coordinateX ?: null,
                'coordinate_y'         => $this->coordinateY ?: null,
                'coordinate_z'         => $this->coordinateZ ?: null,
                'find_number'           => $this->findNumber ?: null,
                'bag_number'            => $this->bagNumber ?: null,
                'find_group'            => $this->findGroup ?: null,
                'find_note'             => $this->findNote ?: null,
                'created_by'            => auth()->id(),
            ]);

            // Buluntu Medyaları
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

            // 2. Eğer sikke ekleme seçildiyse Sikke Oluştur
            if ($this->addCoin) {
                $coin = Coin::create([
                    'find_id'                 => $find->id,
                    'excavation_project_id'   => $this->projectId,
                    'period_id'               => $this->periodId ?: null,
                    'authority_id'            => $this->authorityId ?: null,
                    'ruler_id'                => $this->rulerId ?: null,
                    'region_id'               => $this->regionId ?: null,
                    'mint_id'                 => $this->mintId ?: null,
                    'metal_id'                => $this->metalId ?: null,
                    'denomination_id'         => $this->denominationId ?: null,
                    'date_range'              => $this->dateRange ?: null,
                    'diameter'                => $this->diameter ?: null,
                    'weight'                  => $this->weight ?: null,
                    'axis'                    => $this->axis ?: null,
                    'is_cut'                  => $this->isCut,
                    'is_pierced'              => $this->isPierced,
                    'obverse_description'     => $this->obverseDescription ?: null,
                    'obverse_legend'          => $this->obverseLegend ?: null,
                    'obverse_legend_expanded' => $this->obverseLegendExpanded ?: null,
                    'reverse_description'     => $this->reverseDescription ?: null,
                    'reverse_legend'          => $this->reverseLegend ?: null,
                    'reverse_legend_expanded' => $this->reverseLegendExpanded ?: null,
                    'mint_mark'               => $this->mintMark ?: null,
                    'magistrate'              => $this->magistrate ?: null,
                    'control_mark'            => $this->controlMark ?: null,
                    'monogram'                => $this->monogram ?: null,
                    'countermark'             => $this->countermark ?: null,
                    'is_overstrike'           => $this->isOverstrike,
                    'reference'               => $this->reference ?: null,
                    'note'                    => $this->note ?: null,
                    'created_by'              => auth()->id(),
                ]);

                if ($this->coinObversePhoto) {
                    $coin->addMedia($this->coinObversePhoto)->toMediaCollection('obverse');
                }
                if ($this->coinReversePhoto) {
                    $coin->addMedia($this->coinReversePhoto)->toMediaCollection('reverse');
                }
            }
        });

        $msg = $this->addCoin
            ? __('Buluntu ve Sikke başarıyla kaydedildi.')
            : __('Buluntu başarıyla kaydedildi.');

        $this->dispatch('toast', message: $msg, type: 'success');

        // Formu temizle ve yeni giriş için hazırla (proje ve tarih saklanır)
        $savedProjectId = $this->projectId;
        $savedDate      = $this->findDate;
        $this->reset();
        $this->projectId = $savedProjectId;
        $this->findDate  = $savedDate;
    }

    public function resetForm(): void
    {
        $savedProjectId = $this->projectId;
        $savedDate      = $this->findDate;
        $this->reset();
        $this->projectId = $savedProjectId;
        $this->findDate  = $savedDate ?: now()->format('Y-m-d');
        $this->showResetModal = false;
        $this->resetErrorBag();
        $this->dispatch('toast', message: __('Form alanları sıfırlandı.'), type: 'info');
    }

    public function render()
    {
        return view('pages.quick-entry.index')
            ->layout('layouts.app', ['title' => __('Hızlı Veri Girişi')]);
    }
}
