<?php

namespace Mary\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Dropdown extends Component
{
    public string $uuid;

    public function __construct(
        public ?string $label = null,
        public ?string $icon = 'o-chevron-down',
        public ?bool $right = false,

        //Slots
        public mixed $trigger = null
    ) {
        $this->uuid = "mary" . md5(serialize($this));
    }

    public function render(): View|Closure|string
    {
        return <<<'HTML'
            <details
                x-data="{open: false}"
                @click.outside="open = false"
                :open="open"
                class="dropdown"
            >
                <!-- CUSTOM TRIGGER -->
                @if($trigger)
                    <summary x-ref="button" @click.prevent="open = !open" {{ $trigger->attributes->class(['list-none']) }}>
                        {{ $trigger }}
                    </summary>
                @else
                    <!-- DEFAULT TRIGGER -->
                    <summary x-ref="button" @click.prevent="open = !open" {{ $attributes->class(["btn normal-case"]) }}>
                        {{ $label }}
                        <x-mary-icon :name="$icon" />
                    </summary>
                @endif

                <ul
                    class="p-2 shadow menu z-[1] border border-base-200 bg-base-100 dark:bg-base-200 rounded-box whitespace-nowrap"
                    @click="open = false"
                    x-anchor.{{ $right ? 'bottom-end' : 'bottom-start' }}="$refs.button"
                >
                    <div wire:key="dropdown-slot-{{ $uuid }}">
                    {{ $slot }}
                    </div>
                </ul>
            </details>
        HTML;
    }
}
