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
        public ?string $iconRight = null,
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
                    
                    @if($spinner)
                        wire:target="{{ $spinnerTarget() }}"
                        wire:loading.attr="disabled"                    
                    @endif
                >
                    
                    <!-- SPINNER -->
                    @if($spinner)
                        <span wire:loading wire:target="{{ $spinnerTarget() }}" class="loading loading-spinner w-5 h-5"></span>
                    @endif

                    <!-- ICON -->
                    @if($icon)
                        <span @if($spinner) wire:loading.remove wire:target="{{ $spinnerTarget() }}" @endif>
                            <x-icon :name="$icon" />
                        </span>
                    @endif
                    
                    {{ $label ?? $slot }}

                    <!-- ICON RIGHT -->                    
                    @if($iconRight)
                        <span @if($spinner) wire:loading.remove wire:target="{{ $spinnerTarget() }}" @endif>
                            <x-icon :name="$iconRight" />
                        </span>
                    @endif
                </button>
            HTML;
    }
}
