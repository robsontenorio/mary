<?php

namespace Mary\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Input extends Component
{
    public string $uuid;

    public function __construct(
        public string $name = '',
        public string $label = '',
        public string $icon = '',
        public string $hint = '',
        public string $prefix = '',
        public bool $money = false
    ) {
        $this->uuid = md5(serialize($this));
    }

    public function render(): View|Closure|string
    {
        return <<<'HTML'
                <div wire:key="{{ $uuid }}">         
                    <label class="pt-0 label label-text font-semibold">{{ $label }}</label>
                    <div class="relative">
                        @if($icon)
                            @svg($icon, 'mt-3 ml-3 text-gray-400 absolute')                        
                        @endif
                        
                        @if($prefix)
                            <span class="mt-3 ml-3 text-gray-400 absolute">{{ $prefix }}</span>                            
                        @endif

                        <input {{ $attributes->whereDoesntStartWith('class') }} @if($money) x-mask:dynamic="$money($input, ',', '.')" @endif {{ $attributes->class(['input input-primary w-full', 'pl-10' => ($icon || $prefix), 'input-error' => $errors->has($name)]) }} >
                                                
                        @error($name)
                            <span class="text-red-500 label-text-alt pl-1">{{ $message }}</span>
                        @enderror
                        
                        @if($hint)
                            <span class="label-text-alt text-gray-400 pl-1">{{ $hint }}</span>
                        @endif
                    </div>

                    
                </div>
            HTML;
    }
}
