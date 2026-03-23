<?php

namespace Mary\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Badge extends Component
{
    public string $uuid;

    public function __construct(
        public ?string $id = null,
        public ?string $value = null,
        public ?string $icon = null,
        public ?string $iconRight = null,

    ) {
        $this->uuid = "mary" . md5(serialize($this)) . $id;
    }

    public function render(): View|Closure|string
    {
        return <<<'HTML'
                <div {{ $attributes->class(["badge"])}}>
                    <!-- ICON -->
                    @if($icon)
                        <x-mary-icon :name="$icon" class="h-4 w-4" />
                    @endif

                    <!-- VALUE / SLOT -->
                    @if($value)
                        {{ $value }}
                    @else
                        {{ $slot }}
                    @endif

                    <!-- ICON RIGHT -->
                    @if($iconRight)
                        <x-mary-icon :name="$iconRight" class="h-4 w-4" />
                    @endif
                </div>
            HTML;
    }
}
