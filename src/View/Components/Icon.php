<?php

namespace Mary\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Illuminate\View\Component;

class Icon extends Component
{
    public string $uuid;

    public function __construct(
        public string $name,
        public ?string $class = null,
        private string $defaultClasses = 'w-5 h-5 inline'
    ) {
        $this->uuid = md5(serialize($this));
    }

    public function classes(): string
    {
        if (Str::contains($this->class, ['w-', 'h-'])) {
            return "inline {$this->class}";
        }

        return "{$this->defaultClasses} {$this->class}";
    }

    public function render(): View|Closure|string
    {
        return <<<'HTML'
                @svg("heroicon-{$name}", "$classes()")
            HTML;
    }
}
