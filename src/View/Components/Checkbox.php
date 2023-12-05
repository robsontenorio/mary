<?php

namespace Mary\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Checkbox extends Component
{
    public function __construct(
        public ?string $label = null,
        public ?string $hint = null,
        public ?bool $right = false,
        public ?bool $tight = false
    ) {
    }

    public function modelName(): ?string
    {
        return $this->attributes->whereStartsWith('wire:model')->first();
    }

    public function render(): View|Closure|string
    {
        return <<<'HTML'
                <div>
                    <label class="flex gap-3 items-center cursor-pointer">
                        @if($right)
                            <span @class(["flex-1" => !$tight])>
                                {{ $label}}
                            </span>
                        @endif

                        <input type="checkbox" {{ $attributes->whereDoesntStartWith('class') }} {{ $attributes->class(['checkbox checkbox-primary']) }}  />

                        @if(!$right)
                            {{ $label}}
                        @endif
                    </label>

                    <!-- ERROR -->
                    @error($modelName())
                        <div class="text-red-500 label-text-alt p-1">{{ $message }}</div>
                    @enderror

                    <!-- HINT -->
                    @if($hint)
                        <div class="label-text-alt text-gray-400 py-1 pb-0">{{ $hint }}</div>
                    @endif
                </div>
        HTML;
    }
}
