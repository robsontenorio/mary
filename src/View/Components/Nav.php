<?php

namespace Mary\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Nav extends Component
{
    public function __construct(
        public ?bool $sticky = false,
        public ?bool $fullWidth = false,

        // Slots
        public mixed $brand = null,
        public mixed $actions = null
    ) {
        //
    }

    public function render(): View|Closure|string
    {
        return <<<'HTML'
                @if($sticky)
                    <div class="sticky top-0 z-10">
                @endif

                    <header {{ $attributes->class(["bg-base-100 border-gray-100 border-b"]) }}>
                        <div class="mx-auto px-6 py-5 flex items-center @if(!$fullWidth) max-w-screen-2xl @endif">
                            <div {{ $attributes->class(["flex-1 flex items-center"]) }}>
                                {{ $brand }}
                            </div>
                            <div {{ $attributes->class(["flex items-center gap-4"]) }}>
                                {{ $actions }}
                            </div>
                        </div>
                    </header>

                @if($sticky)
                    </div>
                @endif
                HTML;
    }
}
