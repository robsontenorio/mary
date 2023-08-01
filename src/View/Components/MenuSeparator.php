<?php

namespace Mary\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class MenuSeparator extends Component
{
    public string $uuid;

    public function __construct(
        public string $title = '',
        public string $icon = '',
    ) {
        $this->uuid = md5(serialize($this));
    }

    public function render(): View|Closure|string
    {
        return <<<'HTML'
                <hr class="my-3"/>          
                @if($title)
                <li {{ $attributes->class(["menu-title uppercase"]) }}>
                    <div class="flex items-center gap-2">
                        @if($icon)
                        <x-icon :name="$icon" class="w-4 h-4 inline-flex"  />
                        @endif

                        {{ $title }}
                    </div>
                </li>        
                @endif                      
            HTML;
    }
}
