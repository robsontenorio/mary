<?php

namespace Mary\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Tabs extends Component
{
    public string $uuid;

    public function __construct(
        public string $selected = '',
    ) {
        $this->uuid = md5(serialize($this));
    }

    public function render(): View|Closure|string
    {
        return <<<'HTML'
                    <div class="tabs" x-data="{ selected: '{{ $selected }}' }">
                        {{ $slot }}
                    </div>
                    <hr/>
                    <div id="tab-content" wire:key="{{ $uuid }}">
                            <!-- tab contents will be teleported in here -->                             
                    </div>                    
                HTML;
    }
}
