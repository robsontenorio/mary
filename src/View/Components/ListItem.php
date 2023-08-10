<?php

namespace Mary\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Illuminate\View\Component;

class ListItem extends Component
{
    public string $uuid;

    public function __construct(
        public object $item,
        public string $avatar = 'avatar',
        public string $value = 'name',
        public ?string $subValue = null,
        public ?bool $noSeparator = false,
        public ?string $link = null,

        // Slots
        public mixed $action = null,
    ) {
        $this->uuid = Str::uuid();
    }

    public function render(): View|Closure|string
    {
        return <<<'HTML'
            <div wire:key="{{ $uuid }}">  
                @if($link) 
                    <a href="{{ $link }}" wire:navigate> 
                @endif

                <div 
                    {{ $attributes->class(["flex justify-start items-center gap-4 hover:bg-base-200 p-3"]) }}>
                    
                    <!-- AVATAR -->
                    @if($item->$avatar)
                        <div>                                                    
                            <div class="avatar">
                                <div class="w-11 rounded-full">
                                    <img src="{{ $item->$avatar }}" />
                                </div>
                            </div>                                                        
                        </div>
                    @endif

                    <!-- CONTENT -->
                    <div class="flex-1">
                        <div class="font-semibold">
                            {{ $item->$value }}
                        </div>

                        <div class="text-gray-400 text-sm">
                            {{ is_string($subValue) ? $item->$subValue : $subValue }}
                        </div>                        
                    </div>

                    <!-- ACTION -->
                    <div>
                        {{ $action }}                        
                    </div>
                </div>
                
                @if($link)
                    </a>
                @endif

                @if(!$noSeparator) 
                    <hr /> 
                @endif
            </div>
        HTML;
    }
}
