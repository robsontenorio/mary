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
        return <<<'HTML'
                <form
                    {{ $attributes->whereDoesntStartWith('class') }}
                    {{ $attributes->class(['grid grid-flow-row auto-rows-min gap-3']) }}
                >

                    {{ $slot }}

                    @if ($actions)
                        @if(!$noSeparator)
                            <hr class="my-3" />
                        @endif

                        <div class="flex justify-end gap-3">
                            {{ $actions}}
                        </div>
                    @endif
                </form>
                HTML;
    }
}
