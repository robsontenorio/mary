<?php

namespace Mary\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Toast extends Component
{
    public function __construct(
    ) {
    }

    public function render(): View|Closure|string
    {
        return <<<'HTML'
            <div>                                                 
                @persist('mary-toaster')      
                <div                                                 
                    x-cloak
                    x-data="{ show: false, timer: '', toast: ''}"                  
                    @mary-toast.window="   
                                    clearTimeout(timer);
                                    toast = $event.detail.toast
                                    setTimeout(() => show = true, 100);
                                    timer = setTimeout(() => show = false, $event.detail.toast.timeout);                                        
                                    "                         
                >
                    <div 
                        x-show="show" 
                        x-transition.opacity.scale                
                        x-transition:enter.duration.700ms
                        class="fixed cursor-pointer z-50"
                        :class="toast.position"
                        @click="show = false"
                    >
                        <div class="alert rounded-md" :class="toast.css">
                            <div class="grid">
                                <div x-text="toast.title" class="font-bold"></div>                                    
                                <div x-text="toast.description" class="text-xs"></div>
                            </div>
                        </div>
                    </div>
                </div>                           
                
                <!-- Force Tailwind compile alert types -->
                <span class="hidden alert-info top-10 right-10 alert-success alert-warning alert-error alert"></span>

                <script>
                    window.toast = function(payload){
                        window.dispatchEvent(new CustomEvent('mary-toast', {detail: payload}))
                    }
                </script>
                @endpersist                
            </div>     
            HTML;
    }
}
