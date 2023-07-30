<?php

namespace Mary\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Icon extends Component
{
    public string $uuid;

    public function __construct(public string $name, public string $prefix = 'heroicon')
    {
        $this->uuid = md5(serialize($this));
    }

    public function classes(): string
    {
        return $this->attributes->class(['w-5 h-5'])['class'];
    }

    public function render(): View|Closure|string
    {
        return <<<'HTML'
                @svg($prefix.'-'.$name, "$classes")
            HTML;
    }
}
