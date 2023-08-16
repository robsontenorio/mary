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
        public ?string $sufix = null,
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
            <div>
                <!-- TRADICIONAL LABEL -->
                @if($label && !$inline)
                    <label class="pt-0 label label-text font-semibold">{{ $label }}</label> 
                @endif

                <!-- PREFIX/SUFIX CONTAINER -->
                @if($prefix || $sufix)
                    <div class="flex">                        
                @endif

                <!-- PREFIX -->
                @if($prefix)
                    <div class="rounded-l-lg px-4 flex items-center bg-base-200 border border-base-300">
                        {{ $prefix }}
                    </div>
                @endif

                <div class="flex-1 relative">     
                    <!-- ICON  -->
                    @if($icon)
                        <x-icon :name="$icon" class="absolute top-1/2 -translate-y-1/2 ml-3 text-gray-400 " />
                    @endif
                    
                    <!-- MONEY SETUP -->
                    @if($money)
                        <div x-data="{display: ''}" x-init="display = $wire.{{ $name() }}?.replace('.', '{{ $fractionSeparator }}')">                                
                    @endif

                    <input                
                        id="{{ $uuid }}"                    
                        placeholder = "{{ $attributes->whereStartsWith('placeholder')->first() }} "     
                                                    
                        @if($money)
                            :value="display"
                            x-mask:dynamic="$money($input, '{{ $fractionSeparator}}', '{{ $thousandsSeparator }}')"                                     
                            @input="$wire.{{ $name() }} = $el.value.replaceAll('{{ $thousandsSeparator }}', '').replaceAll('{{ $fractionSeparator }}', '.')"
                        @endif

                        {{
                            $attributes
                            ->merge(['type' => 'text'])
                            ->except('wire:model')
                            ->class([
                                'input input-primary w-full peer', 
                                'pl-10' => ($icon), 
                                'h-14' => ($inline),
                                'pt-3' => ($inline && $label),     
                                'rounded-l-none' => $prefix,
                                'rounded-r-none' => $sufix,
                                'border border-dashed' => $attributes->has('readonly'),
                                'input-error' => $errors->has($name())
                            ]) 
                        }}                                    
                    />

                    <!-- INLINE LABEL -->
                    @if($label && $inline)
                        <label for="{{ $uuid }}" class="absolute text-gray-400 duration-300 transform -translate-y-1 scale-75 top-2 z-10 origin-[0] bg-white rounded dark:bg-gray-900 px-2 peer-focus:px-2 peer-focus:text-blue-600 peer-focus:dark:text-blue-500 peer-placeholder-shown:scale-100 peer-placeholder-shown:-translate-y-1/2 peer-placeholder-shown:top-1/2 peer-focus:top-2 peer-focus:scale-75 peer-focus:-translate-y-1 @if($inline && $icon) left-9 @else left-3 @endif">
                            {{ $label }}
                        </label> 
                    @endif         
                        
                    <!-- MONEY HIDDEN INPUT + END MONEY SETUP -->
                    @if($money)
                            <input type="hidden" {{ $attributes->only('wire:model') }} />    
                        </div>      
                    @endif                                                   

                                   
                </div>

                <!-- SUFIX -->
                @if($sufix)
                    <div class="rounded-r-lg py-3.5 px-4 bg-base-200 border border-base-300">
                        {{ $sufix }}
                    </div>
                @endif

                <!-- END: PREFIX / SUFIX CONTAINER  -->
                @if($prefix || $sufix)
                    </div>
                @endif

                <!-- ERROR -->
                @error($name())
                    <div class="text-red-500 label-text-alt p-1">{{ $message }}</div>
                @enderror
                
                <!-- HINT -->
                @if($hint)
                    <div class="label-text-alt text-gray-400 p-1">{{ $hint }}</div>
                @endif      
            </div>
            HTML;
    }
}
