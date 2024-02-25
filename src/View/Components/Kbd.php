<?php

namespace Mary\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Kbd extends Component
{
    public string $uuid;

    public function __construct(

    ) {
        $this->uuid = "mary" . md5(serialize($this));
    }

    public function render(): View|Closure|string
    {
        return <<<'HTML'
                <kbd
                    wire:key="{{ $uuid }}"
                    {{ $attributes->merge(["class" => "kbd"]) }}
                >
                    {{ $slot }}
                </kbd>
            HTML;
    }
}
