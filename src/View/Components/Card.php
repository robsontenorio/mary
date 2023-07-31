<?php

namespace Mary\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Illuminate\View\Component;

class Card extends Component
{
    public string $uuid;

    public function __construct(
        public string $title = '',
        public string $subtitle = '',
        public bool $separator = false,
        public bool $shadow = false,
        public mixed $menu = '',
        public mixed $actions = '',
        public mixed $figure = '',
    ) {
        $this->uuid = Str::uuid();
    }

    public function render(): View|Closure|string
    {
        return <<<'HTML'
                <div wire:key="{{ $uuid }}" {{ $attributes->class(['card bg-base-100 rounded-lg', 'shadow-sm' => $shadow])}}>
                    <figure>
                        {{ $figure }}
                    </figure>
                    
                        @if($title || $subtitle)
                            <div class="px-5 pt-5">
                                <div class="flex justify-between items-center">
                                    <div>
                                        @if($title)
                                            <div class="text-2xl font-bold">{{ $title }}</div>
                                        @endif
                                        @if($subtitle)
                                            <div class="text-gray-500 text-sm mt-1">{{ $subtitle }}</div>
                                        @endif                        
                                    </div>

                                    @if($menu)
                                        <div> {{ $menu }} </div>
                                    @endif
                                </div>            
                                @if($separator) <hr class="mt-3" /> @endif 
                            </div>                                                
                        @endif                                        
                    <div class="p-5">                        
                        {{ $slot }}
                    </div>
                    @if($actions)                                            
                        @if($separator) <hr class="mt-5" /> @endif
                        <div class="flex justify-end gap-3 p-3">                            
                            {{ $actions }}
                        </div>
                    @endif
                </div>
            HTML;
    }
}
