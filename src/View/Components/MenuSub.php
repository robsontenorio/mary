<?php

namespace Mary\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class MenuSub extends Component
{
    public string $uuid;

    public function __construct(
        public ?string $title = null,
        public ?string $icon = null
    ) {
        $this->uuid = "mary" . md5(serialize($this));
    }

    public function render(): View|Closure|string
    {
        return <<<'HTML'
                @aware(['activeBgColor' => 'bg-base-300'])

                @php
                    $submenuActive = Str::contains($slot, 'mary-active-menu');
                @endphp

                <li
                    x-data="
                    {
                        show: @if($submenuActive) true @else false @endif,
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
                    <details :open="show" @if($submenuActive) open @endif @click.stop>
                        <summary @click.prevent="toggle()" @class(["hover:text-inherit text-inherit", $activeBgColor => $submenuActive])>
                            @if($icon)
                                <x-mary-icon :name="$icon" class="inline-flex"  />
                            @endif
                            <span class="mary-hideable">{{ $title }}</span>
                        </summary>
                        <ul class="mary-hideable">
                            {{ $slot }}
                        </ul>
                    </details>
                </li>
            HTML;
    }
}
