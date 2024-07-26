<?php

namespace Mary\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Rating extends Component
{
    public string $uuid;

    public function __construct(
        public int $total = 5
    ) {
        $this->uuid = "mary" . md5(serialize($this));
    }

    public function modelName(): ?string
    {
        return $this->attributes->whereStartsWith('wire:model')->first();
    }

    public function size(): ?string
    {
        return str($this->attributes->get('class'))->match('/(rating-(..))/');
    }

    public function render(): View|Closure|string
    {
        return <<<'HTML'
                <div class="rating gap-1 {{ $size }}" x-cloak>
                    <!-- NO RATING-->
                    <input
                        type="radio"
                        name="{{ $modelName() }}"
                        value="0"
                        class="rating-hidden hidden"
                        {{ $attributes->whereStartsWith('wire:model') }}
                    />

                    @for ($i = 1; $i <= $total; $i++)
                        <input
                            type="radio"
                            name="{{ $modelName() }}"
                            value="{{ $i }}"
                            {{ $attributes->whereStartsWith('wire:model') }}
                            {{ $attributes->class(["mask mask-star-2"]) }}
                        />
                    @endfor
                </div>
            HTML;
    }
}
