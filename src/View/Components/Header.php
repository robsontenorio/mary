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

        // Slots
        public mixed $middle = null,
        public mixed $actions = null,
    ) {

    }

    public function render(): View|Closure|string
    {
        return <<<'HTML'
                <div {{ $attributes->class(["mb-10"]) }}>
                    <div class="flex justify-between items-center">
                        <div>
                            <div class="{{$size}} font-extrabold">{{ $title }}</div>
                            @if($subtitle)
                                <div class="text-gray-500 text-sm mt-1">{{ $subtitle }}</div>
                            @endif
                        </div>
                        <div class="flex items-center gap-3">
                            {{ $middle }}
                        </div>
                        <div class="flex items-center gap-3">
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
