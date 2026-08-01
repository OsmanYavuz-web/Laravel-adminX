<?php

namespace App\Modules\ExcaCoin\Livewire\Pages\ExcavationProjects;

use App\Models\User;
use App\Modules\ExcaCoin\Models\ExcavationProject;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    public string $filterStatus = 'all'; // all | active | inactive

    // Form alanları
    public ?int $editingId = null;

    public string $formName = '';

    public string $formSiteName = '';

    public string $formLocation = '';

    public string $formCountry = '';

    public ?string $formStartDate = null;

    public ?string $formEndDate = null;

    public string $formDirector = '';

    public string $formDescription = '';

    public bool $formIsActive = true;

    // Modal kontrolleri
    public bool $showProjectModal = false;

    public bool $showDeleteModal = false;

    // Silme onay
    public ?int $deletingId = null;

    // Kullanıcı ataması & Görünür alanlar
    public array $assignedUsers = [];

    public array $visibleFields = [];

    public function mount(): void
    {
        abort_unless(auth()->user()->can('excavation_projects.view') || auth()->user()->hasRole('super-admin'), 403);
    }

    // Seçilebilir Opsiyonel Alan Tanımları
    public static array $availableFindFields = [
        'excavation_season' => 'Kazı Sezonu',
        'sector' => 'Sektör',
        'area' => 'Alan',
        'trench' => 'Açma',
        'square' => 'Kare / Grid',
        'sub_square' => 'Alt Kare',
        'locus' => 'Locus',
        'context' => 'Konteks',
        'stratigraphic_unit' => 'Stratigrafik Birim (SU)',
        'unit' => 'Unit / Birim',
        'layer' => 'Tabaka',
        'level' => 'Seviye',
        'phase' => 'Evre',
        'feature' => 'Feature / Özellik',
        'grave_number' => 'Mezar Numarası',
        'structure' => 'Yapı',
        'room' => 'Mekân',
        'architectural_feature' => 'Mimari Unsur',
        'find_spot' => 'Buluntu Yeri',
        'elevation' => 'Kot (metre)',
        'coordinate_x' => 'Koordinat X',
        'coordinate_y' => 'Koordinat Y',
        'coordinate_z' => 'Koordinat Z',
        'find_number' => 'Buluntu Numarası',
        'bag_number' => 'Torba Numarası',
        'find_group' => 'Buluntu Grubu',
        'find_note' => 'Buluntu Notu',
        'cover_photo' => 'Buluntu Kapak Fotoğrafı',
        'gallery' => 'Buluntu Fotoğraf Galerisi',
        'documents' => 'Buluntu Belgeleri & Çizimler',
    ];

    public static array $availableCoinFields = [
        'period_id' => 'Dönem',
        'authority_id' => 'Otorite / Devlet',
        'ruler_id' => 'Hükümdar / İmparator',
        'region_id' => 'Bölge',
        'mint_id' => 'Darphane',
        'denomination_id' => 'Nominal / Birim',
        'date_range' => 'Tarih Aralığı',
        'metal_id' => 'Metal',
        'diameter' => 'Çap (mm)',
        'weight' => 'Ağırlık (gram)',
        'axis' => 'Kalıp Yönü (Saat)',
        'is_cut' => 'Kesilmiş / Kırpılmış',
        'is_pierced' => 'Delinmiş',
        'obverse_description' => 'Ön Yüz Açıklaması',
        'obverse_legend' => 'Ön Yüz Yazısı (Legend)',
        'obverse_legend_expanded' => 'Ön Yüz Yazısı Açılımı',
        'reverse_description' => 'Arka Yüz Açıklaması',
        'reverse_legend' => 'Arka Yüz Yazısı (Legend)',
        'reverse_legend_expanded' => 'Arka Yüz Yazısı Açılımı',
        'mint_mark' => 'Darphane İşareti',
        'magistrate' => 'Magistrat / Yetkili',
        'control_mark' => 'Kontrol İşareti',
        'monogram' => 'Monogram',
        'countermark' => 'Kontrmark',
        'is_overstrike' => 'Üst Baskı',
        'reference' => 'Katalog Referansı',
        'note' => 'Notlar / Kondisyon',
        'coin_photos' => 'Sikke Ön / Arka Yüz Fotoğrafları',
    ];

    protected function rules(): array
    {
        return [
            'formName' => 'required|string|max:200',
            'formSiteName' => 'required|string|max:200',
            'formLocation' => 'nullable|string|max:200',
            'formCountry' => 'nullable|string|max:100',
            'formStartDate' => 'nullable|date',
            'formEndDate' => 'nullable|date|after_or_equal:formStartDate',
            'formDirector' => 'nullable|string|max:200',
            'formDescription' => 'nullable|string|max:2000',
            'formIsActive' => 'boolean',
            'assignedUsers' => 'nullable|array',
            'assignedUsers.*' => 'exists:users,id',
            'visibleFields' => 'nullable|array',
        ];
    }

    protected function messages(): array
    {
        return [
            'formName.required' => __('Proje adı zorunludur.'),
            'formSiteName.required' => __('Kazı alanı adı zorunludur.'),
            'formEndDate.after_or_equal' => __('Bitiş tarihi başlangıç tarihinden önce olamaz.'),
        ];
    }

    #[Computed]
    public function projects()
    {
        $query = ExcavationProject::accessibleBy()
            ->withCount(['finds', 'coins'])
            ->when($this->search, fn ($q) => $q->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('site_name', 'like', "%{$this->search}%")
                    ->orWhere('location', 'like', "%{$this->search}%")
                    ->orWhere('director', 'like', "%{$this->search}%");
            }))
            ->when($this->filterStatus === 'active', fn ($q) => $q->where('is_active', true))
            ->when($this->filterStatus === 'inactive', fn ($q) => $q->where('is_active', false))
            ->latest();

        return $query->paginate(12);
    }

    #[Computed]
    public function allUsers()
    {
        return User::orderBy('name')->get();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedFilterStatus(): void
    {
        $this->resetPage();
    }

    public function create(): void
    {
        $this->resetForm();
        $this->editingId = null;
        // Varsayılan olarak tüm alanlar seçili gelsin
        $this->visibleFields = array_merge(
            array_keys(static::$availableFindFields),
            array_keys(static::$availableCoinFields)
        );
        $this->showProjectModal = true;
    }

    public function edit(int $id): void
    {
        $project = ExcavationProject::with('users')->findOrFail($id);
        $this->editingId = $id;
        $this->formName = $project->name;
        $this->formSiteName = $project->site_name;
        $this->formLocation = $project->location ?? '';
        $this->formCountry = $project->country ?? '';
        $this->formStartDate = $project->start_date?->format('Y-m-d');
        $this->formEndDate = $project->end_date?->format('Y-m-d');
        $this->formDirector = $project->director ?? '';
        $this->formDescription = $project->description ?? '';
        $this->formIsActive = $project->is_active;
        $this->assignedUsers = $project->users->pluck('id')->toArray();
        $this->visibleFields = $project->visible_fields ?? array_merge(
            array_keys(static::$availableFindFields),
            array_keys(static::$availableCoinFields)
        );
        $this->showProjectModal = true;
    }

    public function selectAllFields(): void
    {
        $this->visibleFields = array_merge(
            array_keys(static::$availableFindFields),
            array_keys(static::$availableCoinFields)
        );
    }

    public function deselectAllFields(): void
    {
        $this->visibleFields = [];
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'name' => $this->formName,
            'site_name' => $this->formSiteName,
            'location' => $this->formLocation ?: null,
            'country' => $this->formCountry ?: null,
            'start_date' => $this->formStartDate ?: null,
            'end_date' => $this->formEndDate ?: null,
            'director' => $this->formDirector ?: null,
            'description' => $this->formDescription ?: null,
            'is_active' => $this->formIsActive,
            'visible_fields' => array_values($this->visibleFields),
        ];

        if ($this->editingId) {
            $project = ExcavationProject::findOrFail($this->editingId);
            $project->update($data);
            $project->users()->sync($this->assignedUsers);
            $message = __('Proje güncellendi.');
        } else {
            $data['created_by'] = auth()->id();
            $project = ExcavationProject::create($data);
            $project->users()->sync($this->assignedUsers);
            $message = __('Proje oluşturuldu.');
        }

        $this->showProjectModal = false;
        $this->dispatch('toast', message: $message, type: 'success');
        $this->resetForm();
    }

    public function toggleActive(int $id): void
    {
        $project = ExcavationProject::findOrFail($id);
        $project->update(['is_active' => ! $project->is_active]);
    }

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

        $project = ExcavationProject::withCount(['finds', 'coins'])->findOrFail($this->deletingId);

        if ($project->finds_count > 0 || $project->coins_count > 0) {
            $this->dispatch('toast', message: __('Bu proje altında buluntu veya sikke kaydı bulunduğu için silinemez.'), type: 'danger');
            $this->showDeleteModal = false;
            $this->deletingId = null;

            return;
        }

        $project->delete();
        $this->showDeleteModal = false;
        $this->dispatch('toast', message: __('Proje silindi.'), type: 'success');
        $this->deletingId = null;
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->formName = '';
        $this->formSiteName = '';
        $this->formLocation = '';
        $this->formCountry = '';
        $this->formStartDate = null;
        $this->formEndDate = null;
        $this->formDirector = '';
        $this->formDescription = '';
        $this->formIsActive = true;
        $this->assignedUsers = [];
        $this->visibleFields = [];
        $this->resetValidation();
    }

    public function render()
    {
        return view('exca-coin::pages.excavation-projects.index')
            ->layout('layouts.app', ['title' => __('Kazı Projeleri')]);
    }
}
