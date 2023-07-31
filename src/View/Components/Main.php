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

    ) {
        //
    }

    public function render(): View|Closure|string
    {
        return <<<'HTML'
                 <main class="overflow-hidden lg:flex lg:flex-1 lg:flex-col">
                    <div {{ $attributes->class(["max-w-screen-2xl w-full mx-auto px-6 flex flex-wrap justify-between gap-10"]) }}>
                        <div
                            {{ $sidebar->attributes->class(["-mx-3 overflow-y-auto custom-scrollbar max-h-[calc(100%-74px)] hidden lg:block py-14 lg:max-w-[240px] lg:w-full", "fixed" => $sidebar->attributes->has('sticky')]) }}>
                            {{ $sidebar }}
                        </div>
                        @if($sidebar->attributes->has('sticky'))
                        <div class="hidden lg:block lg:max-w-[240px] lg:w-full"></div>
                        @endif
                        <div {{ $content->attributes->class(["pt-12 overflow-auto flex-1 mx-auto w-full  lg:max-w-4xl"]) }}>
                            {{ $content }}
                        </div>
                    </div>
                </main>                
                HTML;
    }
}
