<?php

namespace Mary\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Dropdown extends Component
{
    public function __construct(
        public ?string $label = null,
        public ?string $icon = 'o-chevron-down',
        public ?bool $right = false,

        //Slots
        public mixed $trigger = null
    ) {
    }

    public function render(): View|Closure|string
    {
        return <<<'HTML'
            <details
                class="dropdown @if($right) dropdown-end @endif"
                x-data="{open: false}"
                @click.outside="open = false"
                :open="open"
            >
                <!-- CUSTOM TRIGGER -->
                @if($trigger)
                    <summary @click.prevent="open = !open" class="list-none">
                        {{ $trigger }}
                    </summary>
                @else
                    <!-- DEFAULT TRIGGER -->
                    <summary @click.prevent="open = !open" {{ $attributes->class(["btn normal-case"]) }}>
                        {{ $label }}
                        <x-icon :name="$icon" />
                    </summary>
                @endif
                <ul @click="open = false" class="dropdown-content p-2 shadow menu z-[1] bg-base-100 dark:bg-base-200 rounded-box whitespace-nowrap">
                    {{ $slot }}
                </ul>
            </details>
        HTML;
    }
}
