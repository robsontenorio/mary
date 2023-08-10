<?php

namespace Mary\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Illuminate\View\Component;

class Tab extends Component
{
    public string $uuid;

    public function __construct(
        public ?string $name = null,
        public ?string $label = null,
        public ?string $icon = null
    ) {
        $this->uuid = Str::uuid();
    }

    public function render(): View|Closure|string
    {
        return <<<'HTML'
                    <div wire:key="{{ $uuid }}">
                        <a 
                            wire:key="{{ $uuid }}" 
                            @click.prevent="selected = '{{ $name }}'" 
                            :class="{ 'tab-active': selected === '{{ $name }}' }"                              
                            {{ $attributes->whereDoesntStartWith('class') }} 
                            {{ $attributes->class(['tab tab-bordered font-semibold'])}}"> 
                            
                            @if($icon)
                                <x-icon :name="$icon" class="mr-2" />  
                            @endif

                            {{ $label }} 
                        </a>                       
                    
                        <template x-teleport='#tab-content'>
                            <div class="py-5" x-show="selected === '{{ $name }}'">
                                {{ $slot }}
                            </div>
                        </template>
                    </div>        
                HTML;
    }
}
