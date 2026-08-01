<?php

namespace App\Modules\ExcaCoin\Livewire\Pages\Coins;

use App\Modules\ExcaCoin\Models\Coin;
use App\Modules\ExcaCoin\Models\ExcavationProject;
use App\Modules\ExcaCoin\Models\Find;
use Livewire\Component;

class Show extends Component
{
    public ExcavationProject $project;

    public Find $find;

    public Coin $coin;

    public function render()
    {
        $this->coin->load(['period', 'authority', 'ruler', 'region', 'mint', 'metal', 'denomination', 'creator']);

        return view('exca-coin::pages.coins.show')
            ->layout('layouts.app', ['title' => __('Sikke Detayı').' — '.$this->find->inventory_number]);
    }
}
