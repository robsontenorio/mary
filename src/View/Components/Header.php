<?php

namespace Mary\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Header extends Component
{
    public function __construct(
        public string $title = '',
        public string $subtitle = '',
        public bool $separator = false,
        public string $size = '4xl',
        public mixed $actions = ''
    ) {

    }

    public function render(): View|Closure|string
    {
        return <<<'HTML'
                <div class="mb-5">
                    <div class="flex justify-between items-center">
                        <div>
                            <div class="text-{{$size}} font-bold">{{ $title }}</div>
                            <div class="text-gray-500 text-sm mt-1">{{ $subtitle }}</div>
                        </div>
                        <div>
                            {{ $actions}}
                        </div>                                
                    </div>
                    @if($separator) <hr class="my-5" /> @endif 
                </div>                        
                HTML;
    }
}
