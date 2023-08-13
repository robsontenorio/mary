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

    public function spinnerTarget(): ?string
    {
        if ($this->spinner == 1) {
            return $this->attributes->whereStartsWith('wire:click')->first();
        }

        return $this->spinner;
    }

    public function render(): View|Closure|string
    {
        return <<<'HTML'
                <button 
                    wire:key="{{ $uuid }}" 
                    {{ $attributes->whereDoesntStartWith('class') }} 
                    {{ $attributes->class(['btn normal-case']) }}
                    {{ $attributes->merge(['type' => 'button']) }} 
                    
                    wire:target="{{ $spinnerTarget() }}"
                    wire:loading.attr="disabled"                    
                    >
                    

                    @if($spinner)
                        <span wire:loading wire:target="{{ $spinnerTarget() }}" class="loading loading-spinner"></span>
                    @endif

                    @if($icon)
                        <x-icon :name="$icon"   />
                    @endif
                    
                    {{ $label ?? $slot }}
                </button>
            HTML;
    }
}
