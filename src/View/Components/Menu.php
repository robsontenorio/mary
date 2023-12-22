<?php

namespace Mary\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Menu extends Component
{
    public string $uuid;

    public function __construct(
        public ?string $title = null,
        public ?string $icon = null,
        public ?bool $separator = false,
        public ?bool $activateByRoute = false,
        public ?string $activeBgColor = 'bg-base-300',
    ) {
        $this->uuid = "mary" . md5(serialize($this));
    }

    public function render(): View|Closure|string
    {
        return <<<'HTML'
                <ul {{ $attributes->class(["menu rounded-md"]) }} >
                    @if($title)
                        <li class="menu-title text-inherit uppercase">
                            <div class="flex items-center gap-2">

                                @if($icon)
                                    <x-mary-icon :name="$icon" class="w-4 h-4 inline-flex"  />
                                @endif

                                {{ $title }}
                            </div>
                        </li>
                    @endif

                    @if($separator)
                        <hr class="mb-3"/>
                    @endif

                    {{ $slot }}
                </ul>
            HTML;
    }
}
