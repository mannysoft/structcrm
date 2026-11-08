<?php

use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.app')] class extends Component {
    //
};

?>

<div class="h-[calc(100vh-7rem)]">
    <livewire:assistant.chat :show-history="true" />
</div>
