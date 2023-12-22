<?php

namespace Mary\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Drawer extends Component
{
    public string $uuid;

    public function __construct(
        public ?string $id = null,
        public ?bool $right = false,
        public ?string $title = null,
        public ?string $subtitle = null,
        public ?bool $separator = false,
        public ?bool $withCloseButton = false,

        //Slots
        public ?string $actions = null
    ) {
        $this->uuid = "mary" . md5(serialize($this));
    }

    public function id(): string
    {
        return $this->id ?? $this->attributes?->wire('model')->value();
    }

    public function closeAction(): string
    {
        return $this->attributes->has('wire:model') ? '$wire.' . $this->attributes->wire('model')->value() . ' = false' : '$refs.checkbox.checked  = false';
    }

    public function render(): View|Closure|string
    {
        return <<<'HTML'
                <div x-data @class(["drawer absolute z-50", "drawer-end" => $right])>
                    <!-- Toggle visibility  -->
                    <input
                        id="{{ $id() }}"
                        x-ref="checkbox"
                        type="checkbox"
                        class="drawer-toggle"
                        {{ $attributes->wire('model') }} />

                    <div class="drawer-side">
                        <!-- Overlay effect , click outside -->
                        <label for="{{ $id() }}" class="drawer-overlay"></label>

                        <!-- Content -->
                        <x-mary-card
                            :title="$title"
                            :subtitle="$subtitle"
                            :separator="$separator"
                            wire:key="drawer-card"
                            {{ $attributes->except('wire:model')->class(['min-h-screen rounded-none px-8']) }}
                        >
                            @if($withCloseButton)
                                <x-slot:menu>
                                    <x-mary-button icon="o-x-mark" class="btn-ghost btn-sm" @click="{{ $closeAction() }}" />
                                </x-slot:menu>
                            @endif

                            {{ $slot }}

                            @if($actions)
                                <x-slot:actions>
                                    {{ $actions }}
                                </x-slot:actions>
                            @endif
                        </x-mary-card>
                    </div>
                </div>
            HTML;
    }
}
