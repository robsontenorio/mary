<?php

namespace Mary\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class MenuItem extends Component
{
    public string $uuid;

    public function __construct(
        public ?string $title = null,
        public ?string $icon = null,
        public ?string $link = null,
        public ?bool $active = false,
        public ?bool $separator = false
    ) {
        $this->uuid = md5(serialize($this));
    }

    public function render(): View|Closure|string
    {
        return <<<'HTML'
                <li>
                    <a 
                        {{ $attributes->class(["my-0.5", "active" => $active ]) }}
                        @if($link) 
                            href="{{ $link }}" wire:navigate 
                        @endif  
                    >
                    
                    @if($icon) 
                        <x-icon :name="$icon" /> 
                    @endif

                    {{ $title }}
                    </a>
                </li>
            HTML;
    }
}
