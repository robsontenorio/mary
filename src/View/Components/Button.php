<?php

namespace Mary\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Button extends Component
{
    public string $uuid;

    public function __construct(
        public ?string $label = null,
        public ?string $icon = null,
        public ?string $spinner = null
    ) {
        $this->uuid = md5(serialize($this));
    }

    public function render(): View|Closure|string
    {
        return <<<'HTML'
                <button 
                    wire:key="{{ $uuid }}" 
                    {{ $attributes->whereDoesntStartWith('class') }} 
                    {{ $attributes->class(['btn capitalize']) }}
                    {{ $attributes->merge(['type' => 'button']) }}
                    >

                    @if($spinner)
                    <span wire:loading wire:target="{{ $spinner }}" class="loading loading-spinner"></span>
                    @endif

                    @if($icon)
                        <x-icon :name="$icon"  />
                    @endif
                    
                    {{ $label ?? $slot }}
                </button>
            HTML;
    }
}
