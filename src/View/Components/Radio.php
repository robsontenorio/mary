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
        public ?string $key = 'id',
        public ?string $value = 'name',
        public Collection $options = new Collection(),
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
                            name="{{ $name }}"
                            value="{{ $option->$key }}"
                            aria-label="{{ $option->$value }}" 
                            {{ $attributes->whereStartsWith('wire:model') }}
                            {{ $attributes->class(["join-item capitalize btn input-bordered input bg-base-200"]) }}
                            />                    
                    @endforeach
                </div>
            HTML;
    }
}
