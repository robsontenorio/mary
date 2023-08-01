<?php

namespace Mary\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class MenuItem extends Component
{
    public string $uuid;

    public function __construct(
        public string $title = '',
        public string $icon = '',
        public string $link = '',
        public bool $active = false,
        public bool $separator = false
    ) {
        $this->uuid = md5(serialize($this));
    }

    public function render(): View|Closure|string
    {
        return <<<'HTML'
                <li>
                    <a @if($link) href="{{ $link }}" wire:navigate @endif {{ $attributes->class(["my-0.5", "active" => $active ]) }}>
                    
                    @if($icon) 
                    <x-icon :name="$icon" /> 
                    @endif

                    {{ $title }}
                    </a>
                </li>
            HTML;
    }
}
