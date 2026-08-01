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
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Edit extends Component
{
    use WithFileUploads;

    public ExcavationProject $project;
    public Find $find;
    public Coin $coin;

    public string $activeTab = 'identification';

    // Sekme 1
    public ?int $periodId    = null;
    public ?int $authorityId = null;
    public ?int $rulerId     = null;
    public ?int $regionId    = null;
    public ?int $mintId      = null;
    public string $dateRange = '';

    // Sekme 2
    public ?int $metalId        = null;
    public string $diameter     = '';
    public string $weight       = '';
    public ?int $axis           = null;
    public ?int $denominationId = null;
    public bool $isCut          = false;
    public bool $isPierced      = false;

    // Sekme 3
    public string $obverseDescription    = '';
    public string $obverseLegend         = '';
    public string $obverseLegendExpanded = '';

    // Sekme 4
    public string $reverseDescription    = '';
    public string $reverseLegend         = '';
    public string $reverseLegendExpanded = '';

    // Sekme 5
    public string $mintMark    = '';
    public string $magistrate  = '';
    public string $controlMark = '';
    public string $monogram    = '';
    public string $countermark = '';
    public bool   $isOverstrike = false;
    public string $reference   = '';
    public string $note        = '';

    // Yeni medya
    public $newObversePhoto = null;
    public $newReversePhoto = null;
    public array $newGallery   = [];
    public array $newDocuments = [];

    public function mount(): void
    {
        $c = $this->coin;
        $this->periodId    = $c->period_id;
        $this->authorityId = $c->authority_id;
        $this->rulerId     = $c->ruler_id;
        $this->regionId    = $c->region_id;
        $this->mintId      = $c->mint_id;
        $this->metalId     = $c->metal_id;
        $this->denominationId = $c->denomination_id;
        $this->dateRange   = $c->date_range ?? '';
        $this->diameter    = $c->diameter ? (string) $c->diameter : '';
        $this->weight      = $c->weight ? (string) $c->weight : '';
        $this->axis        = $c->axis;
        $this->isCut       = $c->is_cut;
        $this->isPierced   = $c->is_pierced;
        $this->obverseDescription    = $c->obverse_description ?? '';
        $this->obverseLegend         = $c->obverse_legend ?? '';
        $this->obverseLegendExpanded = $c->obverse_legend_expanded ?? '';
        $this->reverseDescription    = $c->reverse_description ?? '';
        $this->reverseLegend         = $c->reverse_legend ?? '';
        $this->reverseLegendExpanded = $c->reverse_legend_expanded ?? '';
        $this->mintMark    = $c->mint_mark ?? '';
        $this->magistrate  = $c->magistrate ?? '';
        $this->controlMark = $c->control_mark ?? '';
        $this->monogram    = $c->monogram ?? '';
        $this->countermark = $c->countermark ?? '';
        $this->isOverstrike = $c->is_overstrike;
        $this->reference   = $c->reference ?? '';
        $this->note        = $c->note ?? '';
    }

    protected function rules(): array
    {
        return [
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
            'newObversePhoto'  => 'nullable|image|max:10240',
            'newReversePhoto'  => 'nullable|image|max:10240',
            'newGallery.*'     => 'nullable|image|max:10240',
            'newDocuments.*'   => 'nullable|file|mimes:pdf,svg,png,jpg,jpeg|max:20480',
        ];
    }

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

    public function removeMedia(int $mediaId): void
    {
        $media = Media::findOrFail($mediaId);
        if ($media->model_id === $this->coin->id && $media->model_type === Coin::class) {
            $media->delete();
            $this->coin->refresh();
        }
    }

    public function save(): void
    {
        $this->validate();

        $this->coin->update([
            'period_id'       => $this->periodId,
            'authority_id'    => $this->authorityId,
            'ruler_id'        => $this->rulerId,
            'region_id'       => $this->regionId,
            'mint_id'         => $this->mintId,
            'metal_id'        => $this->metalId,
            'denomination_id' => $this->denominationId,
            'date_range'      => $this->dateRange ?: null,
            'diameter'        => $this->diameter ?: null,
            'weight'          => $this->weight ?: null,
            'axis'            => $this->axis,
            'is_cut'          => $this->isCut,
            'is_pierced'      => $this->isPierced,
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
        ]);

        if ($this->newObversePhoto) {
            $this->coin->clearMediaCollection('obverse');
            $this->coin->addMedia($this->newObversePhoto)->toMediaCollection('obverse');
        }
        if ($this->newReversePhoto) {
            $this->coin->clearMediaCollection('reverse');
            $this->coin->addMedia($this->newReversePhoto)->toMediaCollection('reverse');
        }
        if (is_array($this->newGallery)) {
            foreach ($this->newGallery as $file) {
                if ($file) $this->coin->addMedia($file)->toMediaCollection('gallery');
            }
        }
        if (is_array($this->newDocuments)) {
            foreach ($this->newDocuments as $file) {
                if ($file) $this->coin->addMedia($file)->toMediaCollection('document');
            }
        }

        $this->dispatch('toast', message: __('Sikke güncellendi.'), type: 'success');
        $this->redirectRoute('coins.index', [$this->project, $this->find], navigate: true);
    }

    public function render()
    {
        return view('pages.coins.edit')
            ->layout('layouts.app', ['title' => __('Sikke Düzenle')]);
    }
}
