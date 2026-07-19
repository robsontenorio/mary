<?php

namespace Mary\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class MenuSub extends Component
{
    public string $uuid;

    public function __construct(
        public ?string $id = null,
        public ?string $title = null,
        public ?string $icon = null,
        public ?string $iconClasses = null,
        public bool $open = false,
        public ?bool $hidden = false,
        public ?bool $disabled = false,
    ) {
        $this->uuid = "mary" . md5(serialize($this)) . $id;
    }

    public function render(): View|Closure|string
    {
        if ($this->hidden === true) {
            return '';
        }

        return <<<'BLADE'
                @aware(['horizontal' => false, 'activeBgColor' => 'bg-base-300'])

                @php
                    $submenuActive = Str::contains($slot, 'mary-active-menu');
                @endphp

                @if ($slot->isNotEmpty())
                <li
                @class(['menu-disabled' => $disabled])
                    x-data="
                    {
                        show: @if(($submenuActive || $open) && !$horizontal) true @else false @endif,
                        toggle(){
                            // From parent Sidebar
                            if (this.collapsed) {
                                this.show = true
                                $dispatch('menu-sub-clicked');
                                return
                            }

                            this.show = !this.show
                        }
                    }"
                >
                    <details
                        :open="show"
                        @click.stop
                        @if($submenuActive && !$horizontal) open @endif
                        @if($horizontal) @click.outside="show = false" @endif
                    >
                        <summary @click.prevent="toggle()" @class(["hover:text-inherit px-4 py-1.5 my-0.5 text-inherit", $activeBgColor => $submenuActive])>
                            @if($icon)
                                <x-mary-icon :name="$icon" @class(['inline-flex my-0.5', $iconClasses]) />
                            @endif

                            <span class="mary-hideable whitespace-nowrap truncate">{{ $title }}</span>
                        </summary>

                        <ul @class(["mary-hideable",  "z-10 mt-1" => $horizontal])>
                            {{ $slot }}
                        </ul>
                    </details>
                </li>
                @endif
                BLADE;
    }
}
