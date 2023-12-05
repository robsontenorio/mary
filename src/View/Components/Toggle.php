<?php

namespace Mary\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Toggle extends Component
{
    public function __construct(
        public ?string $label = null,
        public ?string $hint = null,
        public ?bool $right = false,
        public ?bool $tight = false
    ) {
    }

    public function render(): View|Closure|string
    {
        return <<<'HTML'
                <div>
                    <label class="flex items-center gap-3 cursor-pointer">

                        @if($right)
                            <span @class(["flex-1" => !$tight])>
                                {{ $label}}
                            </span>
                        @endif

                        <input type="checkbox" {{ $attributes->whereDoesntStartWith('class') }} {{ $attributes->class(['toggle toggle-primary']) }}  />

                        @if(!$right)
                            {{ $label}}
                        @endif
                    </label>

                    <!-- HINT -->
                    @if($hint)
                        <div class="label-text-alt text-gray-400 py-1 pb-0">{{ $hint }}</div>
                    @endif
                </div>
        HTML;
    }
}
