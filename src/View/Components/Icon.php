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
        public string $name
    ) {
        $this->uuid = md5(serialize($this));
    }

    public function icon(): string
    {
        $name = Str::of($this->name);

        return $name->contains('.') ? $name->replace('.', '-') : "heroicon-{$this->name}";
    }

    public function render(): View|Closure|string
    {
        return <<<'HTML'
                <x-svg
                    :name="$icon()"
                    {{
                        $attributes->class([
                            'inline',
                            'w-5 h-5' => !Str::contains($attributes->get('class'), ['w-', 'h-'])
                        ])
                     }}
                />
            HTML;
    }
}
