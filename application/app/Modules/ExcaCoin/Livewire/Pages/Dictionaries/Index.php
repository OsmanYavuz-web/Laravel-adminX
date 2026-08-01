<?php

namespace App\Modules\ExcaCoin\Livewire\Pages\Dictionaries;

use App\Models\Language;
use App\Modules\ExcaCoin\Models\Coin;
use App\Modules\ExcaCoin\Models\Dictionary;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Index extends Component
{
    // Seçili tip
    public string $activeType = 'period';

    // Modal kontrolleri
    public bool $showFormModal = false;

    public bool $showDeleteModal = false;

    // Form alanları
    public ?int $editingId = null;

    public array $formName = [];

    public string $formCode = '';

    public int $formSort = 0;

    public bool $formActive = true;

    // Sil onay
    public ?int $deletingId = null;

    public function mount(): void
    {
        abort_unless(auth()->user()->can('dictionaries.view') || auth()->user()->hasRole('super-admin'), 403);
        $this->initLanguages();
    }

    protected function rules(): array
    {
        $defaultLang = Language::getDefault()['code'] ?? 'tr';

        return [
            "formName.{$defaultLang}" => 'required|string|max:150',
            'formCode' => 'nullable|string|max:20',
            'formSort' => 'integer|min:0',
            'formActive' => 'boolean',
        ];
    }

    protected function messages(): array
    {
        $defaultLang = Language::getDefault()['code'] ?? 'tr';

        return [
            "formName.{$defaultLang}.required" => __('Ad / Başlık zorunludur.'),
        ];
    }

    #[Computed]
    public function items(): Collection
    {
        return Dictionary::where('type', $this->activeType)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }

    #[Computed]
    public function types(): array
    {
        return Dictionary::TYPES;
    }

    #[Computed]
    public function activeLanguages(): array
    {
        return Language::getActive();
    }

    public function setType(string $type): void
    {
        $this->activeType = $type;
        $this->resetForm();
    }

    public function create(): void
    {
        $this->resetForm();
        $this->editingId = null;
        $this->showFormModal = true;
    }

    public function edit(int $id): void
    {
        $item = Dictionary::findOrFail($id);
        $this->editingId = $id;

        $languages = Language::getActive();
        $this->formName = [];
        foreach ($languages as $lang) {
            $this->formName[$lang['code']] = $item->getTranslation('name', $lang['code'], false) ?: '';
        }

        $this->formCode = $item->code ?? '';
        $this->formSort = $item->sort_order;
        $this->formActive = $item->is_active;
        $this->showFormModal = true;
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'type' => $this->activeType,
            'code' => $this->formCode ?: null,
            'name' => array_filter($this->formName),
            'sort_order' => $this->formSort,
            'is_active' => $this->formActive,
        ];

        if ($this->editingId) {
            $item = Dictionary::findOrFail($this->editingId);
            $item->update($data);
            $message = __('Kayıt güncellendi.');
        } else {
            $item = Dictionary::create($data);
            $message = __('Kayıt eklendi.');
        }

        $this->showFormModal = false;
        $this->dispatch('toast', message: $message, type: 'success');
        $this->resetForm();
    }

    public function toggleActive(int $id): void
    {
        $item = Dictionary::findOrFail($id);
        $item->update(['is_active' => ! $item->is_active]);
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

        $item = Dictionary::findOrFail($this->deletingId);

        // Kullanımda mı kontrol et
        $usedInCoins = Coin::where("{$this->activeType}_id", $this->deletingId)->exists();
        if ($usedInCoins) {
            $this->dispatch('toast', message: __('Bu kayıt sikke tanımlarında kullanılıyor, silinemez.'), type: 'danger');
            $this->showDeleteModal = false;
            $this->deletingId = null;

            return;
        }

        $item->delete();
        $this->showDeleteModal = false;
        $this->dispatch('toast', message: __('Kayıt silindi.'), type: 'success');
        $this->deletingId = null;
    }

    private function initLanguages(): void
    {
        $languages = Language::getActive();
        $this->formName = [];
        foreach ($languages as $lang) {
            $this->formName[$lang['code']] = '';
        }
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->initLanguages();
        $this->formCode = '';
        $this->formSort = 0;
        $this->formActive = true;
        $this->resetValidation();
    }

    public function render()
    {
        return view('exca-coin::pages.dictionaries.index')
            ->layout('layouts.app', ['title' => __('Sözlükler')]);
    }
}
