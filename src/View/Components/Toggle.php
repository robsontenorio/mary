<?php

namespace Mary\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Toggle extends Component
{
    public function __construct(
        public ?string $label = null,
        public ?bool $right = false
    ) {

    }

    public function render(): View|Closure|string
    {
        return <<<'HTML'
            <div>
                <label class="label label-text font-semibold"> 
                    
                    @if(!$right) 
                        {{ $label}} 
                    @endif
                    
                    <input type="checkbox" {{ $attributes->whereDoesntStartWith('class') }} {{ $attributes->class(['toggle toggle-primary']) }}  />                

                    @if($right) 
                        {{ $label}} 
                    @endif
                    
                </label>
            </div>
        HTML;
    }
}
