<?php

namespace Mary\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\View\Component;

class Radio extends Component
{
    public string $uuid;

    public function __construct(
        public ?string $label = null,
        public ?string $hint = null,
        public ?string $optionValue = 'id',
        public ?string $optionLabel = 'name',
        public Collection|array $options = new Collection(),
    ) {
        $this->uuid = md5(serialize($this));
    }

    public function name(): string
    {
        return $this->attributes->whereStartsWith('wire:model')->first();
    }

    public function render(): View|Closure|string
    {
        return <<<'HTML'
                @if($label)
                    <label class="label label-text font-semibold">{{ $label }}</label>  
                @endif 

                <div class="join">
                    @foreach ($options as $option)
                        <input                             
                            type="radio" 
                            name="{{ $name() }}"
                            value="{{ data_get($option, $optionValue) }}"
                            aria-label="{{ data_get($option, $optionLabel) }}" 
                            {{ $attributes->whereStartsWith('wire:model') }}
                            {{ $attributes->class(["join-item capitalize btn input-bordered input bg-base-200"]) }}
                            />                    
                    @endforeach                    
                </div>

                @error($name())
                    <div class="text-red-500 label-text-alt pl-1">{{ $message }}</div>
                @enderror
                
                @if($hint)
                    <div class="label-text-alt text-gray-400 pl-1 mt-2">{{ $hint }}</div>
                @endif
            HTML;
    }
}
