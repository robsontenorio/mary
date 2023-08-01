<?php

namespace Mary\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Form extends Component
{
    public function __construct(public mixed $actions = '')
    {
        //
    }

    public function render(): View|Closure|string
    {
        return <<<'HTML'
                <div>
                    <form {{ $attributes->whereDoesntStartWith('class') }} {{ $attributes->class(['grid grid-flow-row auto-rows-min gap-2']) }}>
                        {{ $slot }}
                        
                        <hr class="my-3" />

                        <div class="flex justify-end gap-5">                            
                            {{ $actions}}                            
                        </div>
                    </form>
                </div>
                HTML;
    }
}
