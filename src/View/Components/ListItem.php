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
        public mixed $actions = null,
    ) {
        $this->uuid = Str::uuid();
    }

    public function render(): View|Closure|string
    {
        return <<<'HTML'
            <div wire:key="{{ $uuid }}">                  
                <div 
                    {{ $attributes->class([
                        "flex justify-start items-center gap-4 hover:bg-base-200/50 px-3", 
                        "cursor-pointer" => $link]) 
                    }}
                >
                    
                    @if($link) 
                        <a href="{{ $link }}" wire:navigate> 
                    @endif

                    <!-- AVATAR -->
                    @if($item->$avatar)
                        <div class="py-3">                                                    
                            <div class="avatar">
                                <div class="w-11 rounded-full">
                                    <img src="{{ $item->$avatar }}" />
                                </div>
                            </div>                                                        
                        </div>
                    @endif

                    @if(!is_string($avatar))
                        <div class="py-3">
                            {{ $avatar }}
                        </div>
                    @endif                    


                    @if($link)
                        </a>
                    @endif

                    <!-- CONTENT -->                
                    <div class="flex-1 py-3">
                        @if($link) 
                            <a href="{{ $link }}" wire:navigate> 
                        @endif
                        
                        <div class="font-semibold">
                            {{ is_string($value) ? $item->$value : $value }}
                        </div>

                        <div class="text-gray-400 text-sm">
                            {{ is_string($subValue) ? $item->$subValue : $subValue }}
                        </div>                        

                        @if($link)
                            </a>
                        @endif
                    </div>                    

                    <!-- ACTION -->
                    @if($actions)
                        @if($link && !Str::of($actions)->contains([':click', '@click' , 'href']))
                            <a href="{{ $link }}" wire:navigate> 
                        @endif
                            
                            <div class="py-3 flex items-center gap-3">                            
                                    {{ $actions }}                        
                            </div>
                       
                        @if($link && !Str::of($actions)->contains([':click', '@click' , 'href']))
                            </a>
                        @endif
                    @endif
                </div>                            

                @if(!$noSeparator) 
                    <hr /> 
                @endif
            </div>
        HTML;
    }
}
