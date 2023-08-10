<?php

namespace Mary\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Main extends Component
{
    public function __construct(
        public mixed $sidebar = '',
        public mixed $content = '',
        public mixed $footer = ''
    ) {
        //
    }

    public function render(): View|Closure|string
    {
        return <<<'HTML'
                 <main class="flex flex-col">
                    <div class="w-full mx-auto max-w-screen-2xl flex">
                        @if($sidebar)
                        <div {{ $sidebar->attributes->class(["hidden lg:block max-w-[280px] w-full pt-5"]) }}>
                            {{ $sidebar }}
                        </div>
                        @endif
                        <div {{ $content->attributes->class(["min-h-screen flex-1 mx-auto w-full p-5 lg:p-10"]) }}>
                            {{ $content }}
                        </div>                        
                    </div>                  
                    @if($footer)  
                    <div {{ $footer->attributes->class(["max-w-screen-2xl mx-auto w-full"]) }}>
                        {{ $footer }}
                    </div>
                    @endif              

                    @if($sidebar->attributes['drawer']) 
                    <x-drawer id="{{ $sidebar->attributes['drawer'] }}"> 
                        {{ $sidebar }}
                    </x-drawer>                        
                    @endif
                </main>                
                HTML;
    }
}
