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

    public function uuid(): string
    {
        return $this->uuid.$this->attributes->wire('model')->value();
    }

    public function render(): View|Closure|string
    {
        return <<<'HTML'
                    <div
                        x-data="{ selected: @entangle($attributes->wire('model')) }"
                        x-class="scrollbar-none flex-nowrap overflow-x-auto"
                    >
                        <!-- TABS -->
                         <div id="{{ $uuid() }}-labels" wire:ignore {{ $attributes->except(['wire:model', 'wire:model.live'])->class(["tabs tabs-border", $tabsClass]) }}></div>

                        <!-- ORIGINAL DATA -->
                         <div>
                            {{ $slot }}
                         </div>
                    </div>
                HTML;
    }
}
