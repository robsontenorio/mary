<?php

namespace Mary\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Stat extends Component
{
    public string $uuid;

    public string $tooltipPosition = 'lg:tooltip-top';

    public function __construct(
        public ?string $value = null,
        public ?string $icon = null,
        public ?string $color = 'text-primary',
        public ?string $title = null,
        public ?string $description = null,
        public ?string $tooltip = null,
        public ?string $tooltipLeft = null,
        public ?string $tooltipRight = null,
        public ?string $tooltipBottom = null,

    ) {
        $this->uuid = "mary" . md5(serialize($this));
        $this->tooltip = $this->tooltip ?? $this->tooltipLeft ?? $this->tooltipRight ?? $this->tooltipBottom;
        $this->tooltipPosition = $this->tooltipLeft ? 'lg:tooltip-left' : ($this->tooltipRight ? 'lg:tooltip-right' : ($this->tooltipBottom ? 'lg:tooltip-bottom' : 'lg:tooltip-top'));
    }

    public function render(): View|Closure|string
    {
        return <<<'HTML'
                <div
                    {{ $attributes->class(["bg-base-100 rounded-lg px-5 py-4  w-full", "lg:tooltip $tooltipPosition" => $tooltip]) }}

                    @if($tooltip)
                        data-tip="{{ $tooltip }}"
                    @endif
                >
                    <div class="flex items-center gap-3">
                        @if($icon)
                            <div class="  {{ $color }}">
                                <x-mary-icon :name="$icon" class="w-9 h-9" />
                            </div>
                        @endif

                        <div class="text-left">
                            @if($title)
                                <div class="text-xs text-gray-500 whitespace-nowrap">{{ $title }}</div>
                            @endif

                            <div class="font-black text-xl">{{ $value ?? $slot }}</div>

                            @if($description)
                                <div class="stat-desc">{{ $description }}</div>
                            @endif
                        </div>
                    </div>
                </div>
            HTML;
    }
}
