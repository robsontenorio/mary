<?php

namespace Mary\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Dropdown extends Component
{
    public function __construct(
        public ?string $label = null,
        public ?bool $right = false,
        public ?bool $tight = false
    ) {

    }

    public function render(): View|Closure|string
    {
        return <<<'HTML'
            <details class="dropdown mb-32">
                <summary class="m-1 btn">open or close</summary>
                <ul class="p-2 shadow menu dropdown-content z-[1] bg-base-100 rounded-box w-52">
                    <li><a>Item 1</a></li>
                    <li><a>Item 2</a></li>
                </ul>
            </details>                     
        HTML;
    }
}
