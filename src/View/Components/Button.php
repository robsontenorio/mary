<?php

namespace Mary\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Button extends Component
{
    public string $uuid;

    public string $tooltipPosition = 'tooltip-top';

    public function __construct(
        public ?string $label = null,
        public ?string $icon = null,
        public ?string $iconRight = null,
        public ?string $spinner = null,
        public ?string $link = null,
        public ?bool $external = false,
        public ?string $tooltip = null,
        public ?string $tooltipLeft = null,
        public ?string $tooltipRight = null,
        public ?string $tooltipBottom = null,
    ) {
        $this->uuid = md5(serialize($this));
        $this->tooltip = $this->tooltip ?? $this->tooltipLeft ?? $this->tooltipRight ?? $this->tooltipBottom;
        $this->tooltipPosition = $this->tooltipLeft ? 'tooltip-left' : ($this->tooltipRight ? 'tooltip-right' : ($this->tooltipBottom ? 'tooltip-bottom' : 'tooltip-top'));
    }

    public function spinnerTarget(): ?string
    {
        if ($this->spinner == 1) {
            return $this->attributes->whereStartsWith('wire:click')->first();
        }

        return $this->spinner;
    }

    public function render(): View|Closure|string
    {
        return <<<'HTML'
                @if($link)
                    <a href="{{ $link }}"
                @else
                    <button
                @endif

                    wire:key="{{ $uuid }}"
                    {{ $attributes->whereDoesntStartWith('class') }}
                    {{ $attributes->class(['btn normal-case', "lg:tooltip lg:$tooltipPosition" => $tooltip]) }}
                    {{ $attributes->merge(['type' => 'button']) }}

                    @if($link && $external)
                        target="_blank"
                    @endif

                    @if($link && ! $external)
                        wire:navigate
                    @endif

                    @if($tooltip)
                        data-tip="{{ $tooltip }}"
                    @endif

                    @if($spinner)
                        wire:target="{{ $spinnerTarget() }}"
                        wire:loading.attr="disabled"
                    @endif
                >

                    <!-- SPINNER -->
                    @if($spinner)
                        <span wire:loading wire:target="{{ $spinnerTarget() }}" class="loading loading-spinner w-5 h-5"></span>
                    @endif

                    <!-- ICON -->
                    @if($icon)
                        <span @if($spinner) wire:loading.remove wire:target="{{ $spinnerTarget() }}" @endif>
                            <x-icon :name="$icon" />
                        </span>
                    @endif

                    {{ $label ?? $slot }}

                    <!-- ICON RIGHT -->
                    @if($iconRight)
                        <span @if($spinner) wire:loading.remove wire:target="{{ $spinnerTarget() }}" @endif>
                            <x-icon :name="$iconRight" />
                        </span>
                    @endif

                @if(!$link)
                    </button>
                @else
                    </a>
                @endif

                <!--  Force tailwind compile tooltip classes   -->
                <span class="hidden">
                    <span class="lg:tooltip lg:tooltip-left lg:tooltip-right lg:tooltip-bottom lg:tooltip-top"></span>
                </span>
            HTML;
    }
}
