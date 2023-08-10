<?php

namespace Mary\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Header extends Component
{
    public function __construct(
        public ?string $title = null,
        public ?string $subtitle = null,
        public ?bool $separator = false,
        public ?string $size = 'text-4xl',
        public mixed $actions = null
    ) {

    }

    public function render(): View|Closure|string
    {
        return <<<'HTML'
                <div class="mb-5">
                    <div class="flex justify-between items-center">
                        <div>
                            <div class="{{$size}} font-extrabold">{{ $title }}</div>
                            <div class="text-gray-500 text-sm mt-1">{{ $subtitle }}</div>
                        </div>
                        <div>
                            {{ $actions}}
                        </div>                                
                    </div>

                    @if($separator) 
                        <hr class="my-5" /> 
                    @endif 
                </div>                        
                HTML;
    }
}
