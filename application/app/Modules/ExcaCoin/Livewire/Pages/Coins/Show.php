<?php

namespace App\Livewire\Pages\Coins;

use App\Models\Coin;
use App\Models\ExcavationProject;
use App\Models\Find;
use Livewire\Component;

class Show extends Component
{
    public ExcavationProject $project;
    public Find $find;
    public Coin $coin;

    public function render()
    {
        $this->coin->load(['period', 'authority', 'ruler', 'region', 'mint', 'metal', 'denomination', 'creator']);

        return view('pages.coins.show')
            ->layout('layouts.app', ['title' => __('Sikke Detayı') . ' — ' . $this->find->inventory_number]);
    }
}
