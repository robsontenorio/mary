<?php

namespace Mary\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Toast extends Component
{
    public function __construct(
    ) {
    }

    public function render(): View|Closure|string
    {
        return <<<'HTML'
                <livewire:mary::toast :flash="session('mary-flash') ?? []" />
            HTML;
    }
}
