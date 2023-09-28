<?php

namespace Mary\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Tabs extends Component
{
    public string $uuid;

    public function __construct(
        public ?string $selected = null,
        public string $tabContainer = ''
    ) {
        $this->uuid = md5(serialize($this));
        $this->tabContainer = $this->uuid;
    }

    public function selectedTab(): ?string
    {
        if ($this->selected) {
            return "'".$this->selected."'";
        }

        if ($value = $this->attributes->whereStartsWith('wire:model')->first()) {
            return '';
        }
    }

    public function render(): View|Closure|string
    {
        return <<<'HTML'
                    <div 
                        class="flex overflow-x-auto" 
                        x-data="{ 
                            selected:
                                @if($selected) 
                                    '{{ $selected }}'
                                @else  
                                    @entangle($attributes->wire('model')).live 
                                @endif 
                        }"
                    >
                        {{ $slot }}
                    </div>
                    <hr/>
                    <div id="{{ $tabContainer }}">
                            <!-- tab contents will be teleported in here -->                             
                    </div>                    
                HTML;
    }
}
