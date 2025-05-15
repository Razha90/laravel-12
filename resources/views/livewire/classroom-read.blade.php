<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;

new #[Layout('components.layouts.classroom-learn')] class extends Component {
    //
}; ?>

<flux:main class="relative bg-white">
    <flux:sidebar.toggle
        class="text-gray-500! cursor-pointer border transition-all hover:border-gray-400/50 hover:shadow-md lg:hidden"
        icon="bars-2" inset="left" />

</flux:main>
