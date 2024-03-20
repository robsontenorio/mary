<?php

namespace Mary\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class MenuSeparator extends Component
{
    public string $uuid;

    public function __construct(
        public ?string $icon = null,
        public ?string $title = null,
    )
    {
        $this->uuid = "mary" . md5(serialize($this));
    }

    public function render(): View|Closure|string
    {
        return <<<'HTML'
                <div {{ $attributes->class(["divider my-0"]) }}></div>

                @if($title)
                    <x-menu-title :title="$title" :icon="$icon"/>
                @endif
            HTML;
    }
}
