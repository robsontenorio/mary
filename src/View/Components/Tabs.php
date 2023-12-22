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
        public string $tabContainer = ''
    ) {
        $this->uuid = "mary" . md5(serialize($this));
        $this->tabContainer = $this->uuid;
    }

    public function render(): View|Closure|string
    {
        return <<<'HTML'
                    <div
                        x-data="{
                            selected:
                                @if($selected)
                                    '{{ $selected }}'
                                @else
                                    @entangle($attributes->wire('model'))
                                @endif
                        }"

                        {{ $attributes->class(["tabs tabs-bordered flex overflow-x-auto"]) }}
                    >
                        {{ $slot }}
                    </div>
                    <hr/>
                    <div id="{{ $tabContainer }}">
                            <!-- tab contents will be teleported in here -->
                    </div>
                HTML;
    }
}
