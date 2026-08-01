<?php

namespace App\Livewire\Pages\Coins;

use App\Models\Coin;
use App\Models\Dictionary;
use App\Models\ExcavationProject;
use App\Models\Find;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

class Create extends Component
{
    use WithFileUploads;

    public ?ExcavationProject $project = null;
    public ?Find $find = null;
    public ?int $findId = null;

    // Sekme
    public string $activeTab = 'identification';

    // Sekme 1 — Tanımlama
    public ?int $periodId       = null;
    public ?int $authorityId    = null;
    public ?int $rulerId        = null;
    public ?int $regionId       = null;
    public ?int $mintId         = null;
    public string $dateRange    = '';

    // Sekme 2 — Fiziksel
    public ?int $metalId        = null;
    public string $diameter     = '';
    public string $weight       = '';
    public ?int $axis           = null;
    public ?int $denominationId = null;
    public bool $isCut          = false;
    public bool $isPierced      = false;

    // Sekme 3 — Ön Yüz
    public string $obverseDescription      = '';
    public string $obverseLegend           = '';
    public string $obverseLegendExpanded   = '';

    // Sekme 4 — Arka Yüz
    public string $reverseDescription      = '';
    public string $reverseLegend           = '';
    public string $reverseLegendExpanded   = '';

    // Sekme 5 — Ekstra
    public string $mintMark    = '';
    public string $magistrate  = '';
    public string $controlMark = '';
    public string $monogram    = '';
    public string $countermark = '';
    public bool   $isOverstrike = false;
    public string $reference   = '';
    public string $note        = '';

    // Sekme 6 — Medya
    public $obversePhoto  = null;
    public $reversePhoto  = null;
    public array $gallery   = [];
    public array $documents = [];

    protected function rules(): array
    {
        return [
            'findId'         => ($this->find && $this->find->exists) ? 'nullable' : 'required|exists:finds,id',
            'periodId'       => 'nullable|exists:dictionaries,id',
            'authorityId'    => 'nullable|exists:dictionaries,id',
            'rulerId'        => 'nullable|exists:dictionaries,id',
            'regionId'       => 'nullable|exists:dictionaries,id',
            'mintId'         => 'nullable|exists:dictionaries,id',
            'metalId'        => 'nullable|exists:dictionaries,id',
            'denominationId' => 'nullable|exists:dictionaries,id',
            'dateRange'      => 'nullable|string|max:100',
            'diameter'       => 'nullable|numeric|min:0|max:999',
            'weight'         => 'nullable|numeric|min:0|max:9999',
            'axis'           => 'nullable|integer|min:1|max:12',
            'isCut'          => 'boolean',
            'isPierced'      => 'boolean',
            'isOverstrike'   => 'boolean',
            'obverseDescription'    => 'nullable|string|max:2000',
            'obverseLegend'         => 'nullable|string|max:500',
            'obverseLegendExpanded' => 'nullable|string|max:500',
            'reverseDescription'    => 'nullable|string|max:2000',
            'reverseLegend'         => 'nullable|string|max:500',
            'reverseLegendExpanded' => 'nullable|string|max:500',
            'mintMark'    => 'nullable|string|max:200',
            'magistrate'  => 'nullable|string|max:200',
            'controlMark' => 'nullable|string|max:200',
            'monogram'    => 'nullable|string|max:200',
            'countermark' => 'nullable|string|max:200',
            'reference'   => 'nullable|string|max:2000',
            'note'        => 'nullable|string|max:2000',
            'obversePhoto'  => 'nullable|image|max:10240',
            'reversePhoto'  => 'nullable|image|max:10240',
            'gallery.*'     => 'nullable|image|max:10240',
            'documents.*'   => 'nullable|file|mimes:pdf,svg,png,jpg,jpeg|max:20480',
        ];
    }

    protected function messages(): array
    {
        return [
            'findId.required' => __('Buluntu seçimi zorunludur.'),
        ];
    }

    /**
     * QuickAddDictionary'den gelen event — ilgili select'i güncelle
     */
    #[On('dictionary-quick-added')]
    public function onDictionaryQuickAdded(array $payload): void
    {
        $type = $payload['type'] ?? '';
        $id   = $payload['id'] ?? null;

        match ($type) {
            'period'       => $this->periodId = $id,
            'authority'    => $this->authorityId = $id,
            'ruler'        => $this->rulerId = $id,
            'region'       => $this->regionId = $id,
            'mint'         => $this->mintId = $id,
            'metal'        => $this->metalId = $id,
            'denomination' => $this->denominationId = $id,
            default        => null,
        };
    }

    #[Computed]
    public function periods(): \Illuminate\Support\Collection { return Dictionary::ofType('period')->get(); }
    #[Computed]
    public function authorities(): \Illuminate\Support\Collection { return Dictionary::ofType('authority')->get(); }
    #[Computed]
    public function rulers(): \Illuminate\Support\Collection { return Dictionary::ofType('ruler')->get(); }
    #[Computed]
    public function regions(): \Illuminate\Support\Collection { return Dictionary::ofType('region')->get(); }
    #[Computed]
    public function mints(): \Illuminate\Support\Collection { return Dictionary::ofType('mint')->get(); }
    #[Computed]
    public function metals(): \Illuminate\Support\Collection { return Dictionary::ofType('metal')->get(); }
    #[Computed]
    public function denominations(): \Illuminate\Support\Collection { return Dictionary::ofType('denomination')->get(); }

    public function save(): void
    {
        $this->validate();

        $targetFind = ($this->find && $this->find->exists) ? $this->find : Find::with('project')->findOrFail($this->findId);
        $targetProjectId = $targetFind->excavation_project_id;

        $coin = Coin::create([
            'find_id'               => $targetFind->id,
            'excavation_project_id' => $targetProjectId,
            'period_id'             => $this->periodId,
            'authority_id'          => $this->authorityId,
            'ruler_id'              => $this->rulerId,
            'region_id'             => $this->regionId,
            'mint_id'               => $this->mintId,
            'metal_id'              => $this->metalId,
            'denomination_id'       => $this->denominationId,
            'date_range'            => $this->dateRange ?: null,
            'diameter'              => $this->diameter ?: null,
            'weight'                => $this->weight ?: null,
            'axis'                  => $this->axis,
            'is_cut'                => $this->isCut,
            'is_pierced'            => $this->isPierced,
            'obverse_description'        => $this->obverseDescription ?: null,
            'obverse_legend'             => $this->obverseLegend ?: null,
            'obverse_legend_expanded'    => $this->obverseLegendExpanded ?: null,
            'reverse_description'        => $this->reverseDescription ?: null,
            'reverse_legend'             => $this->reverseLegend ?: null,
            'reverse_legend_expanded'    => $this->reverseLegendExpanded ?: null,
            'mint_mark'    => $this->mintMark ?: null,
            'magistrate'   => $this->magistrate ?: null,
            'control_mark' => $this->controlMark ?: null,
            'monogram'     => $this->monogram ?: null,
            'countermark'  => $this->countermark ?: null,
            'is_overstrike' => $this->isOverstrike,
            'reference'    => $this->reference ?: null,
            'note'         => $this->note ?: null,
            'created_by'   => auth()->id(),
        ]);

        // Medya yükleme
        if ($this->obversePhoto) {
            $coin->addMedia($this->obversePhoto)->toMediaCollection('obverse');
        }
        if ($this->reversePhoto) {
            $coin->addMedia($this->reversePhoto)->toMediaCollection('reverse');
        }
        if (is_array($this->gallery)) {
            foreach ($this->gallery as $file) {
                if ($file) $coin->addMedia($file)->toMediaCollection('gallery');
            }
        }
        if (is_array($this->documents)) {
            foreach ($this->documents as $file) {
                if ($file) $coin->addMedia($file)->toMediaCollection('document');
            }
        }

        $this->dispatch('toast', message: __('Sikke kaydedildi.'), type: 'success');
        $this->redirectRoute('coins.index', [$targetFind->project, $targetFind], navigate: true);
    }

    public function render()
    {
        return view('pages.coins.create')
            ->layout('layouts.app', ['title' => __('Yeni Sikke')]);
    }
}
