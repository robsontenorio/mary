<?php

namespace Mary\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class MenuSeparator extends Component
{
    public string $uuid;

    public function __construct(
        public ?string $id = null,
        public ?string $title = null,
        public ?string $icon = null,
        public ?string $iconClasses = null,
    ) {
        $this->uuid = "mary" . md5(serialize($this)) . $id;
    }

    public function render(): View|Closure|string
    {
        return <<<'BLADE'
                <hr class="my-3 border-t-[length:var(--border)] border-base-content/10"/>

                @if($title)
                    <li {{ $attributes->class(["menu-title text-inherit uppercase"]) }}>
                        <div class="flex items-center gap-2">

                            @if($icon)
                                <x-mary-icon :name="$icon" @class([$iconClasses]) />
                            @endif

                            {{ $title }}
                        </div>
                    </li>
                @endif
            BLADE;
    }
}
