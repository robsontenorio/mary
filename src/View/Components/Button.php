<?php

namespace Mary\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Button extends Component
{
    public string $uuid;

    public function __construct(public string $label = '', public string $icon = '')
    {
        $this->uuid = md5(serialize($this));
    }

    public function render(): View|Closure|string
    {
        return <<<'HTML'
                <button wire:key="{{ $uuid }}" {{ $attributes->whereDoesntStartWith('class') }} {{ $attributes->class(['btn capitalize']) }}>
                    @if($icon)
                        <x-icon :name="$icon"  />            
                    @endif
                    
                    @if($label)
                        {{ $label }}
                    @else
                        {{ $slot }}
                    @endif  
                </button>
            HTML;
    }
}
