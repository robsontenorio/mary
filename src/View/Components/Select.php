<?php

namespace Mary\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\View\Component;

class Select extends Component
{
    public string $uuid;

    public function __construct(
        public ?string $label = null,
        public ?string $icon = null,
        public ?string $hint = null,
        public ?string $placeholder = null,
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
            <div wire:key="{{ $uuid }}">
                @if($label)
                <label class="label label-text font-semibold">{{ $label }}</label>
                @endif
                
                <div class="relative">
                    @if($icon)
                        <x-icon :name="$icon" class="mt-3 ml-3 text-gray-400 absolute" />                     
                    @endif
                    <select {{ $attributes->whereDoesntStartWith('class') }} {{ $attributes->class(['select select-primary w-full font-normal', 'pl-10' => $icon]) }}>
                        @if($placeholder)
                            <option>{{ $placeholder }}</option>
                        @endif

                        @foreach ($options as $option)
                            <option value="{{ $option[$optionValue] }}">{{ $option[$optionLabel] }}</option>
                        @endforeach
                    </select>
                </div>

                @error($name)
                    <div class="text-red-500">{{ $message }}</div>
                @enderror

                @if($hint)
                    <div class="label-text-alt text-gray-400 pl-1 mt-2">{{ $hint }}</div>
                @endif
            </div>
        HTML;
    }
}
