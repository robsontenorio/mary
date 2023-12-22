<?php

namespace Mary\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Progress extends Component
{
    public string $uuid;

    public function __construct(
        public ?int $value = 0,
        public ?int $max = 100,
        public ?bool $indeterminate = false,

    ) {
        $this->uuid = "mary" . md5(serialize($this));
    }

    public function render(): View|Closure|string
    {
        return <<<'HTML'
                <progress
                    {{ $attributes->class("progress") }}

                    @if(!$indeterminate)
                        value="{{ $value }}"
                        max="{{ $max }}"
                    @endif
                ></progress>
            HTML;
    }
}
