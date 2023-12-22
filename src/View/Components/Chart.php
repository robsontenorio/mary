<?php

namespace Mary\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Chart extends Component
{
    public string $uuid;

    public function __construct()
    {
        $this->uuid = "mary" . md5(serialize($this));
    }

    public function render(): View|Closure|string
    {
        return <<<'HTML'
                <div
                    wire:key="{{ $uuid }}-{{ rand() }}"
                    x-data="{
                        settings: @entangle($attributes->wire('model')),
                        init(){
                            new Chart($refs.chart, this.settings);
                        }
                    }"

                    {{ $attributes->class(["relative"]) }}
                >
                    <canvas x-ref="chart"></canvas>
                </div>
            HTML;
    }
}
