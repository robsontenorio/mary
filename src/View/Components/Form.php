<?php

namespace Mary\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Form extends Component
{
    public function __construct(

        // Slots
        public mixed $actions = null,
        public ?bool $noSeparator = false,
    ) {
        //
    }

    public function render(): View|Closure|string
    {
        return <<<'BLADE'
                <form
                    {{ $attributes->whereDoesntStartWith('class') }}
                    {{ $attributes->class(['grid grid-flow-row auto-rows-min gap-3']) }}
                >

                    {{ $slot }}

                    @if ($actions)
                        @if(!$noSeparator)
                            <hr class="border-t-[length:var(--border)] border-base-content/10 my-3" />
                        @else
                            <div></div>
                        @endif

                        <div {{ $actions->attributes->class(["flex justify-end gap-3"]) }}>
                            {{ $actions}}
                        </div>
                    @endif
                </form>
                BLADE;
    }
}
