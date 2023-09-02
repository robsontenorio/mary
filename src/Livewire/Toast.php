<?php

namespace Mary\Livewire;

use Livewire\Attributes\On;
use Livewire\Attributes\Reactive;
use Livewire\Component;

/**
 * Flash strategy = when it happend form url redirection
 * Event stragety = when there is no url redirection
 */
class Toast extends Component
{
    #[Reactive]
    public array $flash = [];

    public array $toast = [];

    public function mount()
    {
        $this->toast = $this->flash['toast'] ?? [
            'css' => '',
            'icon' => 's-check-circle',
            'title' => '',
            'description' => '',
            'timeout' => '',
            'position' => '',
        ];
    }

    /**
     * For event strategy.
     */
    #[On('mary-toast')]
    public function notify(mixed $toast): void
    {
        $this->toast = $toast;
    }

    public function render()
    {
        return <<<'HTML'
            <div>                                                            
                <div                                                 
                    x-cloak
                    x-data="{ show: false, timer: '', position: 'top-0 right-0' }"                                                                         
                    x-init="
                            @if($flash)
                                clearTimeout(timer);
                                position = '{{ $toast['position'] }}';
                                timeout = {{ $toast['timeout'] }};
                                setTimeout(() => show = true, 1);
                                timer = setTimeout(() => show = false, timeout);
                            @endif
                            "
                    @mary-toast.window="   
                                    clearTimeout(timer);
                                    position = $event.detail.toast.position;
                                    timeout = $event.detail.toast.timeout;
                                    setTimeout(() => show = true, 1);
                                    timer = setTimeout(() => show = false, timeout);                                        
                                    "                         
                >
                    <div 
                        x-show="show" 
                        x-transition.opacity.scale                
                        x-transition:enter.duration.700ms
                        class="fixed cursor-pointer z-50"
                        :class="position"
                        @click="show = false"
                    >
                        <x-alert :class="$toast['css']">
                            <div class="flex items-center">
                                <div>
                                    <x-icon :name="$toast['icon']" class="w-7 h-7 mr-3" />
                                </div>
                                <div>
                                    <div class="font-bold">
                                        {{ $toast['title'] }} 
                                    </div>                                    
                                    <div class="text-xs">
                                        {{ $toast['description'] }}
                                    </div>
                                </div>
                            </div>
                        </x-alert>
                    </div>
                </div>                           
            </div>
        HTML;
    }
}
