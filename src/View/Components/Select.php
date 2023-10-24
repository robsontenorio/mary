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
        public ?string $iconRight = null,
        public ?string $hint = null,
        public ?string $placeholder = null,
        public ?string $placeholderValue = null,
        public ?bool $inline = false,
        public ?string $optionValue = 'id',
        public ?string $optionLabel = 'name',
        public Collection|array $options = new Collection(),
    ) {
        $this->uuid = md5(serialize($this));
    }

    public function modelName(): ?string
    {
        return $this->attributes->whereStartsWith('wire:model')->first();
    }

    public function render(): View|Closure|string
    {
        return <<<'HTML'
            <div wire:key="{{ $uuid }}">
                <!-- STANDARD LABEL -->
                @if($label && !$inline)
                    <label for="{{ $uuid }}" class="pt-0 label label-text font-semibold">{{ $label }}</label>
                @endif

                <div class="relative">
                    <select
                        id="{{ $uuid }}"
                        {{ $attributes->whereDoesntStartWith('class') }}
                        {{ $attributes->class([
                                    'select select-primary w-full font-normal',
                                    'pl-10' => ($icon),
                                    'h-14' => ($inline),
                                    'pt-3' => ($inline && $label),
                                    'select-error' => $errors->has($modelName())
                                ])
                        }}

                    >
                        @if($placeholder)
                            <option value="{{ $placeholderValue }}">{{ $placeholder }}</option>
                        @endif

                        @foreach ($options as $option)
                            <option value="{{ $option[$optionValue] }}" @if(isset($option['disabled'])) disabled @endif>{{ $option[$optionLabel] }}</option>
                        @endforeach
                    </select>

                    <!-- ICON -->
                    @if($icon)
                        <x-icon :name="$icon" class="absolute pointer-events-none top-1/2 -translate-y-1/2 left-3 text-gray-400" />
                    @endif

                    <!-- RIGHT ICON  -->
                    @if($iconRight)
                        <x-icon :name="$iconRight" class="absolute pointer-events-none top-1/2 right-8 -translate-y-1/2 text-gray-400 " />
                    @endif

                    <!-- INLINE LABEL -->
                    @if($label && $inline)
                        <label for="{{ $uuid }}" class="absolute pointer-events-none text-gray-500 duration-300 transform -translate-y-1 scale-75 top-2 origin-[0] rounded bg-base-100 px-2 peer-focus:px-2 peer-focus:text-blue-600 peer-focus:dark:text-blue-500 peer-placeholder-shown:scale-100 peer-placeholder-shown:-translate-y-1/2 peer-placeholder-shown:top-1/2 peer-focus:top-2 peer-focus:scale-75 peer-focus:-translate-y-1 @if($inline && $icon) left-9 @else left-3 @endif">
                            {{ $label }}
                        </label>
                    @endif
                </div>

                 <!-- ERROR -->
                 @error($modelName())
                    <div class="text-red-500 label-text-alt p-1">{{ $message }}</div>
                @enderror

                <!-- HINT -->
                @if($hint)
                    <div class="label-text-alt text-gray-400 pl-1 mt-2">{{ $hint }}</div>
                @endif
            </div>
        HTML;
    }
}
