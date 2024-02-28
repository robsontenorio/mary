<?php

namespace Mary\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Accordion extends Component
{
    public string $uuid;

    public function __construct(
        public ?bool $noJoin = false,
    ) {
        $this->uuid = "mary" . md5(serialize($this));
    }

    public function render(): View|Closure|string
    {
        return <<<'HTML'
                <div
                    x-data="{ model: @entangle($attributes->wire('model')) }"
                    {{ $attributes->whereDoesntStartWith('wire:model')->merge(['class' => ($noJoin ? '' : 'join join-vertical w-full')]) }}
                    wire:key="accordion-{{ $uuid }}"
                >
                        {{ $slot }}
                </div>
            HTML;
    }
}
