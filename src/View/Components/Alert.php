<?php

namespace Mary\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Alert extends Component
{
    public string $uuid;

    public function __construct(public string $text = '', public string $icon = '')
    {
        $this->uuid = md5(serialize($this));
    }

    public function render(): View|Closure|string
    {
        return <<<'HTML'
                <span class="mary"  wire:key="{{ $uuid }}" {{ $attributes }}>
                    <span class="bg-gray-500">---yyxxxsx!!</span>
                </span>
            HTML;
    }
}
