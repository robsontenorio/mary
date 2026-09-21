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
                @if($popover)
                    x-data="{
                        open: false,
                        placed: false,
                        reposition: null,
                        init() {
                            this.reposition = () => this.place()

                            // Capture, so scrolling any ancestor keeps the panel on its trigger.
                            document.addEventListener('scroll', this.reposition, true)
                            window.addEventListener('resize', this.reposition)
                        },
                        destroy() {
                            document.removeEventListener('scroll', this.reposition, true)
                            window.removeEventListener('resize', this.reposition)
                        },
                        place() {
                            const panel = this.$refs.content

                            if (! panel || ! panel.matches(':popover-open')) {
                                return
                            }

                            const gap = 8
                            const trigger = this.$refs.button.getBoundingClientRect()
                            const { width, height } = panel.getBoundingClientRect()
                            const above = trigger.top - height
                            const left = {{ $right ? 'trigger.right - width' : 'trigger.left' }}

                            // A panel wider than its trigger would hang off screen next to a
                            // collapsed sidebar, so keep it within the viewport.
                            panel.style.left = Math.round(
                                Math.min(Math.max(gap, left), window.innerWidth - width - gap)
                            ) + 'px'
                            panel.style.top = Math.round(
                                trigger.bottom + height > window.innerHeight && above >= 0
                                    ? above
                                    : trigger.bottom
                            ) + 'px'

                            this.placed = true
                        },
                    }"
                @else
                    x-data="{open: false}"
                @endif
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
                        :class="{ 'invisible': ! placed }"
                        @toggle="open = ($event.newState === 'open')"
                        x-effect="
                            if (open) {
                                $refs.content.showPopover()
                                $nextTick(() => place())
                            } else {
                                placed = false
                                $refs.content.hidePopover()
                            }
                        "
                    @elseif(!$noXAnchor)
                        x-anchor.{{ $right ? 'bottom-end' : 'bottom-start' }}="$refs.button"
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
