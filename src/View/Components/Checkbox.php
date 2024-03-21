<?php

namespace Mary\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\ViewErrorBag;
use Illuminate\View\Component;
use Mary\Traits\HasErrors;

class Checkbox extends Component
{
    use HasErrors;

    public function __construct(
        public ?string $label = null,
        public ?string $hint = null,
        public ?bool   $right = false,
        public ?bool   $tight = false,
    )
    {
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
                    {!! $errorTemplate($errors) !!}

                    <!-- HINT -->
                    @if($hint)
                        <div class="label-text-alt text-gray-400 py-1 pb-0">{{ $hint }}</div>
                    @endif
                </div>
        HTML;
    }
}
