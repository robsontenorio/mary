<?php

namespace Mary\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Popover extends Component
{
    public string $uuid;

    public function __construct(
        public ?string $position = "bottom",
        public ?string $offset = "10",

        // Slots
        public mixed $trigger = null,
        public mixed $content = null

    ) {
        $this->uuid = "mary" . md5(serialize($this));
    }

    public function render(): View|Closure|string
    {
        return <<<'HTML'
                <div
                    x-cloak
                    x-data="{
                            open: false,
                            timer: null,
                            show() {
                                this.open = true
                                clearTimeout(this.timer)
                            },
                            hide(){
                                this.timer = setTimeout(() => this.open = false, 300)
                            }
                        }"
                >
                  <!-- TRIGGER -->
                  <div
                    x-ref="myTrigger"
                    @mouseover="show()"
                    @mouseout="hide()"
                    {{ $trigger->attributes->class(["w-fit cursor-pointer"]) }}
                  >
                    {{ $trigger }}
                  </div>

                  <!-- CONTENT -->
                  <div
                    x-show="open"
                    x-anchor.{{ $position }}.offset.{{ $offset }}="$refs.myTrigger"
                    @mouseover="show()"
                    @mouseout="hide()"
                    {{ $content->attributes->class(["z-[1] shadow-xl border w-fit p-3 rounded-md bg-base-100"]) }}
                  >
                    {{ $content }}
                  </div>
                </div>
            HTML;
    }
}
