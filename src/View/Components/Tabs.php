<?php

namespace Mary\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Tabs extends Component
{
    public string $uuid;

    public function __construct(
        public ?string $id = null,
        public bool $box = false,
        public bool $lift = false,
        public ?string $labelClass = null,
        public ?string $activeClass = null,
        public ?string $contentClass = null,
        public string $tabsClass = 'scrollbar-none flex-nowrap overflow-x-auto',
    ) {
        $this->uuid = "mary" . md5(serialize($this)) . $id;
    }

    public function render(): View|Closure|string
    {
        return <<<'HTML'
                    <div
                        x-data="{ tabs: [], selected: @entangle($attributes->wire('model')) }"
                        x-class="scrollbar-none flex-nowrap overflow-x-auto"
                        x-init="
                             Array.from($refs.slot.children).forEach(tab => {
                                 $refs.labels.appendChild(tab.querySelector('label'));
                                 $refs.contents.appendChild(tab.querySelector('.tab-content'));
                            });
                        "
                    >
                        <!-- TABS -->
                         <div
                            x-ref="labels"
                            {{ $attributes->except(['wire:model', 'wire:model.live'])->class(["tabs", $tabsClass, "tabs-border" => !$box && !$lift , "tabs-lift" => $lift , "tabs-box" => $box]) }}
                         >
                        </div>

                        <!--  CONTENTS -->
                         <div x-ref="contents"></div>

                         <div data-tab x-ref="slot">
                            {{ $slot }}
                         </div>
                    </div>
                HTML;
    }
}
