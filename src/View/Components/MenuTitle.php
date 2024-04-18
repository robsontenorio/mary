<?php

namespace Mary\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class MenuTitle extends Component
{
    public string $uuid;

    public function __construct(
        public ?string $title = null,
        public ?string $icon = null,
    ) {
        $this->uuid = 'mary'.md5(serialize($this));
    }

    public function render(): View|Closure|string
    {
        return <<<'HTML'
                <li {{ $attributes->class(["menu-title"]) }}>
                    <div class="flex items-center gap-2">

                        @if($icon)
                            <x-mary-icon :name="$icon"  />
                        @endif

                        {{ $title }}
                    </div>
                </li>
            HTML;
    }
}
