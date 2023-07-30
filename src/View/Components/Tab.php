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
        public string $name = '',
        public string $label = '',
        public string $icon = ''
    ) {
        $this->uuid = Str::uuid();
    }

    public function render(): View|Closure|string
    {
        return <<<'HTML'
                    <div wire:key="{{ $uuid }}">
                        <a @click.prevent="selected = '{{ $name }}'" :class="{ 'tab-active': selected === '{{ $name }}' }"  wire:key="{{ $uuid }}" {{ $attributes }} {{ $attributes->class(['tab tab-bordered font-semibold'])}}"> 
                            @if($icon) @svg($icon, 'mr-2 h-5 h-5') @endif
                            {{ $label }} 
                        </a>                       
                    
                        @teleport('#tab-content')
                        <div class="py-5" x-show="selected === '{{ $name }}'">
                            {{ $slot }}
                        </div>
                        @endteleport
                    </div>        
                HTML;
    }
}
