<?php

namespace Mary\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Tabs extends Component
{
    public string $uuid;

    public function __construct(
        public ?string $selected = null,
        public string $labelClass = 'font-semibold',
        public string $activeClass = 'border-b-2 border-b-base-content/50',
        public string $labelDivClass = 'border-b-2 border-b-base-content/10 flex overflow-x-auto',
        public string $tabsClass = 'relative w-full',
    ) {
        $this->uuid = "mary" . md5(serialize($this));
    }

    public function render(): View|Closure|string
    {
        return <<<'HTML'
                    <div
                        x-data="{
                                tabs: [],
                                selected:
                                    @if($selected)
                                        '{{ $selected }}'
                                    @else
                                        @entangle($attributes->wire('model'))
                                    @endif
                                 ,
                                 init() {
                                     // Fix weird issue when navigating back
                                     document.addEventListener('livewire:navigating', () => {
                                         document.querySelectorAll('.tab').forEach(el =>  el.remove());
                                     });
                                 }
                        }"
                        class="{{ $tabsClass }}"
                        x-class="font-semibold border-b-2 border-b-base-content/50 border-b-base-content/10 flex overflow-x-auto scrollbar-hide relative w-full"
                    >
                        <!-- TAB LABELS -->
                        <div class="{{ $labelDivClass }}">
                            <template x-for="tab in tabs">
                                <a
                                    role="tab"
                                    x-html="tab.label"
                                     @click="tab.disabled ? null: selected = tab.name"
                                    :class="(selected === tab.name) && '{{ $activeClass }} tab-active'"
                                    class="tab {{ $labelClass }}"></a>
                            </template>
                        </div>

                        <!-- TAB CONTENT -->
                        <div role="tablist" {{ $attributes->except(['wire:model', 'wire:model.live'])->class(["block"]) }}>
                            {{ $slot }}
                        </div>
                    </div>
                HTML;
    }
}
