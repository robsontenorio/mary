<?php

namespace Mary\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Mary\Traits\WithHtmlId;
use Mary\Traits\WithUuid;

class Tabs extends Component
{
    use WithHtmlId;
    use WithUuid;

    public function __construct(
        public ?string $selected = null,
        public string $tabContainer = ''
    ) {
        $this->setUuid(md5(serialize($this)));
        $this->setHtmlId('tabs');
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
                    <div id="{{ $htmlId }}">
                            <!-- tab contents will be teleported in here -->
                    </div>
                HTML;
    }
}
