<?php

namespace Mary\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Button extends Component
{
    public string $uuid;

    public string $tooltipPosition = 'lg:tooltip-top';

    public function __construct(
        public ?string $label = null,
        public ?string $icon = null,
        public ?string $iconRight = null,
        public ?string $spinner = null,
        public ?string $link = null,
        public ?bool $external = false,
        public ?bool $noWireNavigate = false,
        public ?bool $responsive = false,
        public ?string $badge = null,
        public ?string $badgeClasses = null,
        public ?string $tooltip = null,
        public ?string $tooltipLeft = null,
        public ?string $tooltipRight = null,
        public ?string $tooltipBottom = null,
    ) {
        $this->uuid = "mary" . md5(serialize($this));
        $this->tooltip = $this->tooltip ?? $this->tooltipLeft ?? $this->tooltipRight ?? $this->tooltipBottom;
        $this->tooltipPosition = $this->tooltipLeft ? 'lg:tooltip-left' : ($this->tooltipRight ? 'lg:tooltip-right' : ($this->tooltipBottom ? 'lg:tooltip-bottom' : 'lg:tooltip-top'));
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
                    <a href="{!! $link !!}"
                @else
                    <button
                @endif

                    wire:key="{{ $uuid }}"
                    {{ $attributes->whereDoesntStartWith('class')->merge(['type' => 'button']) }}
                    {{ $attributes->class(['btn normal-case', "!inline-flex lg:tooltip $tooltipPosition" => $tooltip]) }}

                    @if($link && $external)
                        target="_blank"
                    @endif

                    @if($link && !$external && !$noWireNavigate)
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

                    <!-- SPINNER LEFT -->
                    @if($spinner && !$iconRight)
                        <span wire:loading wire:target="{{ $spinnerTarget() }}" class="loading loading-spinner w-5 h-5"></span>
                    @endif

                    <!-- ICON -->
                    @if($icon)
                        <span class="block" @if($spinner) wire:loading.class="hidden" wire:target="{{ $spinnerTarget() }}" @endif>
                            <x-mary-icon :name="$icon" />
                        </span>
                    @endif

                    <!-- LABEL / SLOT -->
                    @if($label)
                        <span @class(["hidden lg:block" => $responsive ])>
                            {{ $label }}
                        </span>
                        @if(strlen($badge ?? '') > 0)
                            <span class="badge badge-primary {{ $badgeClasses }}">{{ $badge }}</span>
                        @endif
                    @else
                        {{ $slot }}
                    @endif

                    <!-- ICON RIGHT -->
                    @if($iconRight)
                        <span class="block" @if($spinner) wire:loading.class="hidden" wire:target="{{ $spinnerTarget() }}" @endif>
                            <x-mary-icon :name="$iconRight" />
                        </span>
                    @endif

                    <!-- SPINNER RIGHT -->
                    @if($spinner && $iconRight)
                        <span wire:loading wire:target="{{ $spinnerTarget() }}" class="loading loading-spinner w-5 h-5"></span>
                    @endif

                @if(!$link)
                    </button>
                @else
                    </a>
                @endif
            HTML;
    }
}
