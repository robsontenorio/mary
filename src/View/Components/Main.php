<?php

namespace Mary\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\Component;

class Main extends Component
{
    public function __construct(

        // Slots
        public mixed $sidebar = null,
        public mixed $content = null,
        public mixed $footer = null,
        public ?bool $fullWidth = false,
        public ?bool $withNav = false,
        public ?string $collapseText = 'Collapse',
        public ?bool $collapsible = false,
    ) {
        // Add if not exists
        Cache::add('mary-sidebar-collapsed', 'false');
    }

    public function render(): View|Closure|string
    {
        return <<<'HTML'
                 <main @class(["flex mx-auto", "max-w-screen-2xl" => !$fullWidth])>   
                    
                    <!-- SIDEBAR -->
                    @if($sidebar)                        
                        <div @class(["h-screen sticky top-0", "top-20" => $withNav])>                                      
                            <div>                                                                
                                <div 
                                    x-data="
                                        {
                                            collapsed: {{ cache('mary-sidebar-collapsed') }},
                                            collapseText: '{{ $collapseText }}',
                                            toggle() {
                                                this.collapsed = !this.collapsed;     
                                                fetch('/mary/toogle-sidebar?collapsed=' + this.collapsed); 
                                            }
                                        }"
                                    @menu-sub-clicked="if(collapsed) { toggle() }"
                                >
                                    <div  
                                        :class="collapsed 
                                                    ? '!w-[70px] [&>*_summary::after]:!hidden [&_.mary-hideable]:!hidden [&_.display-when-collapsed]:!block [&_.hidden-when-collapsed]:!hidden' 
                                                    : '!w-[270px] [&>*_summary::after]:!block [&_.mary-hideable]:!block [&_.hidden-when-collapsed]:!block [&_.display-when-collapsed]:!hidden'"                                        

                                        {{ 
                                            $sidebar->attributes->class([
                                                "hidden lg:block h-screen transition-all duration-100 ease-out overflow-y-auto overflow-x-hidden",
                                                "pb-24" => $withNav,
                                                "w-[70px] [&>*_summary::after]:hidden [&_.mary-hideable]:hidden [&_.display-when-collapsed]:block [&_.hidden-when-collapsed]:hidden" => cache('mary-sidebar-collapsed') == 'true',
                                                "w-[270px] [&>*_summary::after]:block [&_.mary-hideable]:block [&_.hidden-when-collapsed]:block [&_.display-when-collapsed]:hidden" => cache('mary-sidebar-collapsed') != 'true'
                                            ]) 
                                        }}                                  
                                    >              
                                        {{ $sidebar }}

                                        <!-- SIDEBAR COLLAPSE -->
                                        @if($sidebar->attributes['collapsible'])
                                            <x-menu class="fixed bottom-0 hidden bg-inherit lg:block">
                                                <x-menu-item 
                                                    @click="toggle"
                                                    icon="o-bars-3-bottom-right"
                                                    title="{{ $sidebar->attributes['collapse-text'] ?? $collapseText }}" />
                                            </x-menu>
                                        @endif
                                    </div>                    
                                </div>
                            </div>
                        </div>                        
                    @endif

                    <!-- MAIN CONTENT -->
                    <div {{ $content->attributes->class(["min-h-screen mx-auto w-full p-5 lg:p-10"]) }}>
                        {{ $content }}                        
                    </div>              
                                                                                       
                    <!-- DRAWER FOR SIDEBAR -->
                    @if($sidebar?->attributes['drawer']) 
                        <x-drawer id="{{ $sidebar->attributes['drawer'] }}"> 
                            {{ $sidebar }}
                        </x-drawer>                        
                    @endif
                </main>   

                 <!-- FOOTER -->
                 @if($footer)  
                    <div 
                        {{ 
                            $footer->attributes->class([
                                    "mx-auto w-full",
                                    "max-w-screen-2xl" => !$fullWidth
                                ]) 
                        }}
                    >
                        {{ $footer }}
                    </div>
                @endif                    
                HTML;
    }
}
