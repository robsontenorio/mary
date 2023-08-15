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
        public bool $inline = false,
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
                    <div class="relative">
                        @if($label && !$inline)
                        <label class="pt-0 label label-text font-semibold">{{ $label }}</label> 
                        @endif

                        @if($icon)
                            <x-icon :name="$icon" class="absolute top-1/2 -translate-y-1/2 ml-3 text-gray-400 " />
                        @endif
                        
                        @if($prefix)
                            <span class="top-1/2 -translate-y-1/2 ml-3 text-gray-400">{{ $prefix }}</span>                            
                        @endif

                        @if($money)
                            <div x-data="{display: ''}" x-init="display = $wire.{{ $name() }}?.replace('.', '{{ $fractionSeparator }}')" >                                
                                <input                
                                    id="{{ $uuid }}"                    
                                    :value="display"
                                    x-mask:dynamic="$money($input, '{{ $fractionSeparator}}', '{{ $thousandsSeparator }}')"                                     
                                    @input="$wire.{{ $name() }} = $el.value.replaceAll('{{ $thousandsSeparator }}', '').replaceAll('{{ $fractionSeparator }}', '.')"
                                    {{
                                         $attributes
                                            ->merge(['type' => 'text', 'placeholder' => "."])
                                            ->except('wire:model')
                                            ->class([
                                                'input input-primary w-full', 
                                                'pl-10' => ($icon || $prefix), 
                                                'h-14' => ($inline),
                                                'pt-3' => ($inline && $label),
                                                'input-error' => $errors->has($name())
                                            ]) 
                                    }}                                    
                                />
                                
                                <input                               
                                    type="hidden"                                
                                    {{ $attributes->only('wire:model') }}
                                />              
                            </div>       
                        @else
                            <input            
                                id="{{ $uuid }}"                                
                                placeholder = "{{ $attributes->whereStartsWith('placeholder')->first() }} "     
                                
                                {{ 
                                    $attributes                                        
                                        ->class([
                                            'input input-primary w-full peer', 
                                            'pl-10' => ($icon || $prefix), 
                                            'h-14' => ($inline),
                                            'pt-3' => ($inline && $label),
                                            'border border-dashed' => $attributes->has('readonly'),
                                            'input-error' => $errors->has($name())
                                        ]) 
                                        ->merge(['type' => 'text'])
                                }}
                            />
                        @endif

                        @if($label && $inline)
                            <label for="{{ $uuid }}" class="absolute text-gray-400 duration-300 transform -translate-y-1 scale-75 top-2 z-10 origin-[0] bg-white rounded dark:bg-gray-900 px-2 peer-focus:px-2 peer-focus:text-blue-600 peer-focus:dark:text-blue-500 peer-placeholder-shown:scale-100 peer-placeholder-shown:-translate-y-1/2 peer-placeholder-shown:top-1/2 peer-focus:top-2 peer-focus:scale-75 peer-focus:-translate-y-1 @if($inline && $icon) left-9 @else left-3 @endif">
                                {{ $label }}
                            </label> 
                        @endif

                        @error($name())
                            <span class="text-red-500 label-text-alt pl-1">{{ $message }}</span>
                        @enderror
                        
                        @if($hint)
                            <span class="label-text-alt text-gray-400 pl-1">{{ $hint }}</span>
                        @endif
                    </div>
            HTML;
    }
}
