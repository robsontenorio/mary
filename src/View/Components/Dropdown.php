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
        public mixed $trigger = null
    ) {
        $this->uuid = "mary" . md5(serialize($this));
    }

    public function render(): View|Closure|string
    {
        return <<<'HTML'
            <div x-data="{ open: false }" class="relative dropdown">
                <!-- CUSTOM TRIGGER -->
                @if($trigger)
                    <div x-ref="dropdownButton" @click="open = !open" {{ $trigger->attributes->class(['cursor-pointer']) }}>
                        {{ $trigger }}
                    </div>
                @else
                    <div x-ref="dropdownButton" @click="open = !open" {{ $attributes->class(["btn normal-case cursor-pointer"]) }}>
                        {{ $label }}
                        <x-mary-icon :name="$icon" />
                    </div>
                @endif

                <template x-if="open">
                    <div
                        class="fixed inset-0 z-50 items-center justify-center"
                        @click="open = false"
                    >
                        <div
                            x-show="open"
                            x-transition:enter="transition ease-out duration-100"
                            x-transition:enter-start="opacity-0 scale-95"
                            x-transition:enter-end="opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-75"
                            x-transition:leave-start="opacity-100 scale-100"
                            x-transition:leave-end="opacity-0 scale-95"
                            class="absolute z-50 w-auto p-2 border shadow border-base-200 bg-base-100 dark:bg-base-200 rounded-box min-w-max menu"
                            :style="{'top': ($refs.dropdownButton.getBoundingClientRect().bottom + window.scrollY) + 'px', 'left': ($refs.dropdownButton.getBoundingClientRect().left + window.scrollX) + 'px'}"
                        >
                            <div wire:key="dropdown-slot-{{ $uuid }}">
                                {{ $slot }}
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        HTML;
    }
}
