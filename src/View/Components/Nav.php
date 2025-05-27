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
                    <div {{ $attributes->class(["bg-base-100 border-base-content/10 border-b-[length:var(--border)]", "sticky top-0 z-10" => $sticky]) }}>
                        <div @class(["flex items-center px-6 py-3",  "max-w-screen-2xl mx-auto" => !$fullWidth])>
                            <div {{ $brand?->attributes->class(["flex-1 flex items-center"]) }}>
                                {{ $brand }}
                            </div>
                            <div {{ $actions?->attributes->class(["flex items-center gap-4"]) }}>
                                {{ $actions }}
                            </div>
                        </div>
                    </div>
                HTML;
    }
}
