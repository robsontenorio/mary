<?php

namespace Mary\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Input extends Component
{
    public string $uuid;

    public function __construct(
        public ?string $label = null,
        public ?string $icon = null,
        public ?string $hint = null,
        public ?string $prefix = null,
        public bool $money = false,
        public string $thousandsSeparator = ',',
        public string $fractionSeparator = '.',
    ) {
        $this->uuid = md5(serialize($this));
    }

    public function name(): ?string
    {
        return $this->attributes->whereStartsWith('wire:model')->first();
    }

    public function render(): View|Closure|string
    {
        return <<<'HTML'
                <div wire:key="{{ $uuid }}">
                    @if($label)
                        <label class="pt-0 label label-text font-semibold">{{ $label }}</label> 
                    @endif

                    <div class="relative">
                        @if($icon)
                            <x-icon :name="$icon" class="mt-3 ml-3 text-gray-400 absolute" />
                        @endif
                        
                        @if($prefix)
                            <span class="mt-3 ml-3 text-gray-400 absolute">{{ $prefix }}</span>                            
                        @endif

                        @if($money)
                            <div x-data="{display: ''}" x-init="display = $wire.{{ $name() }}.replace('.', '{{ $fractionSeparator }}')" >                                
                                <input                                    
                                    :value="display"
                                    {{
                                         $attributes
                                            ->merge(['type' => 'text'])
                                            ->except('wire:model')
                                            ->class([
                                                'input input-primary w-full', 
                                                'pl-10' => ($icon || $prefix), 
                                                'input-error' => $errors->has($name())
                                            ]) 
                                    }}                                         
                                    
                                    x-mask:dynamic="$money($input, '{{ $fractionSeparator}}', '{{ $thousandsSeparator }}')" 
                                    
                                    @input="$wire.{{ $name() }} = $el.value.replaceAll('{{ $thousandsSeparator }}', '').replaceAll('{{ $fractionSeparator }}', '.')" 
                                />
                                
                                <input                               
                                    type="hidden"                                
                                    {{ $attributes->only('wire:model') }}
                                />              
                            </div>       
                        @else
                            <input                                                
                                {{ 
                                    $attributes
                                        ->merge(['type' => 'text'])
                                        ->class([
                                            'input input-primary w-full', 
                                            'pl-10' => ($icon || $prefix), 
                                            'input-error' => $errors->has($name())]) 
                                }}
                            />
                        @endif

                        @error($name())
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
