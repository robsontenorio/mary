<?php

namespace Mary\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Accordion extends Component
{
    public string $uuid;

    public function __construct(
        public ?bool $join = false,

    public mixed $actions = null
    ) {
        $this->uuid = "mary" . md5(serialize($this));
    }

    public function render(): View|Closure|string
    {
        return <<<'HTML'
                <div
                    x-data="{ join: {{ $join ? 'true' : 'false' }} }"
                    {{ $attributes->merge(['class' => ($join ? 'join join-vertical w-full' : '')]) }}
                    wire:key="{{ $uuid }}">
                        {{ $slot }}
                </div>
            HTML;
    }
}
