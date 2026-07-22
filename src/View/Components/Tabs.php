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
                        x-data="{
                            selected: @entangle($attributes->wire('model')),
                            init() {
                                this.refresh();
                                Livewire.hook('morphed', ({ el }) => this.refresh() );
                            },
                            refresh() {
                                Array.from($refs.slot?.children ?? [])?.forEach(tab => {
                                    const label = tab.querySelector('label');

                                    if (label) {
                                        $nextTick(() => {
                                            $refs.labels.appendChild(label.cloneNode(true));
                                            label.hidden = true
                                        });
                                    }
                                });
                            }
                        }"
                        x-class="scrollbar-none flex-nowrap overflow-x-auto"
                    >
                        <!-- TABS -->
                         <div x-ref="labels" {{ $attributes->except(['wire:model', 'wire:model.live'])->class(["tabs tabs-border", $tabsClass]) }}></div>

                        <!-- ORIGINAL DATA -->
                         <div x-ref="slot">
                            {{ $slot }}
                         </div>
                    </div>
                HTML;
    }
}
