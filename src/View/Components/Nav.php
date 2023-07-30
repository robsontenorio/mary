<?php

namespace Mary\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Nav extends Component
{
    public function __construct(
        public string $background = 'base-100',
        public string $text = '',
        public mixed $brand = '',
        public mixed $actions = ''
    ) {
        //
    }

    public function render(): View|Closure|string
    {
        return <<<'HTML'
                <div class="navbar bg-{{ $background }} {{ $text }}">
                    <div class="flex-1">                        
                        {{ $brand }}                        
                    </div>
                    <div class="flex-none gap-8">
                        {{ $actions }}
                    </div>
                </div>
                HTML;
    }
}
