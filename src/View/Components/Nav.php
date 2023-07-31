<?php

namespace Mary\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Nav extends Component
{
    public function __construct(
        public string $background = 'bg-base-100',
        public mixed $brand = '',
        public bool $sticky = false,
        public mixed $actions = ''
    ) {
        //
    }

    public function render(): View|Closure|string
    {
        return <<<'HTML'
                @if($sticky) 
                <div class="sticky top-0 z-10"> 
                @endif
                    <header class="backdrop-blur-2xl border-gray-200 border-b">                        
                        <div class="max-w-screen-2xl mx-auto px-6 py-4 flex items-center justify-between gap-6">
                            <div class="flex-1">                        
                                {{ $brand }}                        
                            </div>
                            <div class="flex-none gap-8">
                                {{ $actions }}
                            </div>
                        </div>
                    </header>
                @if($sticky) 
                </div>                
                @endif
                HTML;
    }
}
