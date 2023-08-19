<?php

namespace Mary\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Dropdown extends Component
{
    public function __construct(
        public string $label,
        public ?string $icon = 'o-chevron-down',
        public ?bool $right = false
    ) {

    }

    public function render(): View|Closure|string
    {
        return <<<'HTML'
            <span x-data="{open: false}">
                <span @click.outside="open = false">
                    <details class="dropdown @if($right) dropdown-end @endif" :open="open">
                        <summary  
                            @click.prevent="open = !open" 
                            wire:loading.attr="disabled"
                            {{ $attributes->class(["m-1 btn normal-case"]) }}
                        >
                            {{ $label }}

                            <x-icon :name="$icon" />
                        </summary>
                        <ul @click="open = false" class="dropdown-content p-2 shadow menu z-[1] bg-base-100 rounded-box whitespace-nowrap">
                            {{ $slot }}
                        </ul>
                    </details>             
                </span>
            </span>
        HTML;
    }
}
