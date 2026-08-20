<?php

namespace Mary\View\Components;

use Closure;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Dropdown extends Component
{
    public string $uuid;

    public function __construct(
        public ?string $id = null,
        public ?string $label = null,
        public ?string $icon = 'o-chevron-down',
        public ?bool $right = false,
        public ?bool $top = false,
        public ?bool $noXAnchor = false,
        public ?bool $scroll = false,
        public ?string $maxHeight = 'max-h-96',
        public ?bool $popover = false,
        // Slots
        public mixed $trigger = null
    ) {
        $this->uuid = "mary" . md5(serialize($this)) . $id;

        if ($this->popover && $this->noXAnchor) {
            throw new Exception("Cannot use `popover` combined with `no-x-anchor`.");
        }
    }

    public function render(): View|Closure|string
    {
        return <<<'HTML'
            <details
                x-data="{open: false}"
                @click.outside="open = false"
                :open="open"
                @class([
                    'overflow-visible',
                    'dropdown',
                    'dropdown-end' => ($noXAnchor && $right),
                    'dropdown-top' => ($noXAnchor && $top),
                    'dropdown-bottom' => $noXAnchor,
                ])
            >
                <!-- CUSTOM TRIGGER -->
                @if($trigger)
                    <summary x-ref="button" @click.prevent="open = !open" {{ $trigger->attributes->class(['list-none']) }}>
                        {{ $trigger }}
                    </summary>
                @else
                    <!-- DEFAULT TRIGGER -->
                    <summary x-ref="button" @click.prevent="open = !open" {{ $attributes->class(["btn"]) }}>
                        {{ $label }}
                        <x-mary-icon :name="$icon" />
                    </summary>
                @endif

                <ul
                    @class([
                        'p-2','shadow','menu','z-[1]','border-[length:var(--border)]','border-base-content/10','bg-base-100', 'rounded-box','w-auto','min-w-max',
                        'dropdown-content' => $noXAnchor,
                        $maxHeight => $scroll,
                        'overflow-y-auto' => $scroll,
                        'inset-auto m-0 [&[popover]_.mary-hideable]:!block' => $popover,
                    ])
                    @click="open = false"
                    @if($popover)
                        popover
                        x-ref="content"
                        @toggle="open = ($event.newState === 'open')"
                        x-effect="open ? $refs.content.showPopover() : $refs.content.hidePopover()"
                    @endif
                    @if(!$noXAnchor)
                        x-anchor{{ $popover ? '.fixed' : '' }}.{{ $right ? 'bottom-end' : 'bottom-start' }}="$refs.button"
                    @endif
                >
                    <div wire:key="dropdown-slot-{{ $uuid }}">
                        {{ $slot }}
                    </div>
                </ul>
            </details>
        HTML;
    }
}
