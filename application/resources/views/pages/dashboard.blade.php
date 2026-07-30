<?php

use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Title('Dashboard')] #[Layout('layouts.app')] class extends Component {
};
?>

<div class="flex items-center justify-center h-64">
    <p class="text-zinc-500">{{ __('Dashboard') }}</p>
</div>
