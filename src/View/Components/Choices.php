<?php

namespace Mary\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\View\Component;

class Choices extends Component
{
    public function __construct(
        public ?string $label = null,
        public ?string $icon = null,
        public ?string $hint = null,
        public bool $inline = false,
        public Collection|array $options = new Collection(),
    ) {
    }

    public function modelName()
    {
        return $this->attributes->wire('model')->value();
    }

    public function render(): View|Closure|string
    {
        return <<<'HTML'
            <div>
                <!-- STANDARD LABEL -->
                @if($label && !$inline)
                    <label class="pt-0 label label-text font-semibold">{{ $label }}</label> 
                @endif            
            
                <div x-data="{ open: false, display: ''}" @click.outside="open = false" class="relative">                    
                    <div class="relative">

                        <!-- SELECTION DISPLAY -->
                        <span 
                            class="bg-base-100 top-2 pt-1.5 h-8 @if($icon) left-8 @else left-3 @endif px-2 rounded-md font-semibold text-sm underline decoration-dotted hover:bg-base-300 cursor-pointer absolute" 
                            x-show="!open && display"
                            x-text="display"
                            @click="open = true; $refs.searchInput.focus()">
                        </span>

                        <!-- SEARCH INPUT  -->
                        <input         
                            x-ref="searchInput"
                            wire:keyup.debounce="search($el.value)"
                            @focus="open = true;"
                            :value="display"
                            placeholder = "{{ $attributes->whereStartsWith('placeholder')->first() }} "
                            
                            {{ 
                                $attributes
                                    ->except('wire:model')
                                    ->class([
                                        'select select-primary w-full',
                                        'pl-10' => ($icon), 
                                        'h-14' => ($inline),
                                        'pt-3' => ($inline && $label),     
                                        'select-error' => $errors->has($modelName())
                                    ]) 
                            }}  
                        />

                        <!-- ICON  -->
                        @if($icon)
                            <x-icon :name="$icon" class="absolute top-1/2 -translate-y-1/2 left-3 text-gray-400 " />
                        @endif
                    </div>
                            
                    <!-- OPTIONS CONTAINER -->
                    <div x-show="open" @click="open = false" class="relative">        
                        
                        <!-- PROGRESS -->
                        <progress wire:loading.delay wire:target="search" class="progress absolute progress-primary h-0.5"></progress>
                        
                        <!-- OPTIONS -->
                        <div class="absolute w-full bg-base-100 z-10 top-2 border border-base-300 shadow-lg cursor-pointer rounded-lg">                        
                            @forelse($options as $option)
                                <div 
                                    @click="$wire.{{ $modelName() }} = {{ $option->id }}; display = '{{ $option->name }}'"
                                    :class="$wire.{{ $modelName() }} == {{ $option->id }} && 'bg-base-200'"
                                >                    
                                    <x-list-item :item="$option"  />
                                </div>
                            @empty
                            <div class="p-3">No results</div>
                            @endforelse
                        </div>
                    </div>

                    <!-- HIDDEN SELECTED VALUE -->
                    <input type="hidden" {{ $attributes->only('wire:model') }} />

                    <!-- ERROR -->
                    @error($modelName())
                        <div class="text-red-500 label-text-alt p-1">{{ $message }}</div>
                    @enderror
                    
                    <!-- HINT -->
                    @if($hint)
                        <div class="label-text-alt text-gray-400 p-1 pb-0">{{ $hint }}</div>
                    @endif    
                </div>
            </div>
        HTML;
    }
}
