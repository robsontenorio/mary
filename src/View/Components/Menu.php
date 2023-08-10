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
        public ?bool $separator = false
    ) {
        $this->uuid = md5(serialize($this));
    }

    public function render(): View|Closure|string
    {
        return <<<'HTML'
                <ul class="menu rounded-md">
                    
                    @if($title)
                        <li class="menu-title uppercase">
                            <div class="flex items-center gap-2">
                                
                                @if($icon)
                                    <x-icon :name="$icon" class="w-4 h-4 inline-flex"  />
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
