<?php

namespace App\Modules\ExcaCoin\Livewire\Components;

use App\Models\Language;
use App\Modules\ExcaCoin\Models\Dictionary;
use Livewire\Component;

class QuickAddDictionary extends Component
{
    public string $type = '';

    public string $modalName = '';

    public array $name = [];

    public string $code = '';

    public bool $showModal = false;

    public function mount(): void
    {
        $this->initLanguages();
    }

    protected function rules(): array
    {
        $defaultLang = Language::getDefault()['code'] ?? 'tr';

        return [
            "name.{$defaultLang}" => 'required|string|max:150',
            'code' => 'nullable|string|max:20',
        ];
    }

    protected function messages(): array
    {
        $defaultLang = Language::getDefault()['code'] ?? 'tr';

        return [
            "name.{$defaultLang}.required" => __('Ad / Başlık zorunludur.'),
        ];
    }

    public function openModal(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        $primaryName = array_filter($this->name)[app()->getLocale()] ?? reset($this->name) ?? '';

        $item = Dictionary::create([
            'type' => $this->type,
            'code' => $this->code ?: null,
            'name' => array_filter($this->name),
            'sort_order' => 0,
            'is_active' => true,
        ]);

        // Üst bileşene bildir: yeni eklenen ID ve isim
        $this->dispatch('dictionary-quick-added', [
            'type' => $this->type,
            'id' => $item->id,
            'name' => $primaryName,
        ]);

        $this->showModal = false;
        $this->dispatch('toast', message: __('Kayıt eklendi.'), type: 'success');
        $this->resetForm();
    }

    private function initLanguages(): void
    {
        $languages = Language::getActive();
        $this->name = [];
        foreach ($languages as $lang) {
            $this->name[$lang['code']] = '';
        }
    }

    private function resetForm(): void
    {
        $this->initLanguages();
        $this->code = '';
        $this->resetValidation();
    }

    public function render()
    {
        $typeLabel = Dictionary::TYPES[$this->type][app()->getLocale()] ?? $this->type;

        return view('exca-coin::livewire.components.quick-add-dictionary', [
            'typeLabel' => $typeLabel,
        ]);
    }
}
