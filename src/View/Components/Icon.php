<?php

namespace Mary\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Illuminate\Support\Stringable;
use Illuminate\View\Component;

class Icon extends Component
{
    public string $uuid;

    public function __construct(
        public string $name,
        public ?string $id = null,
        public ?string $label = null
    ) {
        $this->uuid = "mary" . md5(serialize($this)) . $id;
    }

    public function icon(): string|Stringable
    {
        $name = Str::of($this->name);

        return $name->contains('.') ? $name->replace('.', '-') : "heroicon-{$this->name}";
    }

    public function labelClasses(): ?string
    {
        // Remove `w-*` and `h-*` classes, because it applies only for icon
        return Str::replaceMatches('/(w-\w*)|(h-\w*)/', '', $this->attributes->get('class') ?? '');
    }

    public function render(): View|Closure|string
    {
        return <<<'BLADE'
                @if(strlen($label ?? '') > 0)
                    <div class="inline-flex items-center gap-1">
                @endif
                    <x-svg
                        :name="$icon()"
                        {{ $attributes->class(['inline flex-shrink-0', 'w-5 h-5' => !Str::contains($attributes->get('class') ?? '', ['w-', 'h-']) ]) }}
                    />

                @if(strlen($label ?? '') > 0)
                        <div class="{{ $labelClasses() }}" {{ $attributes->whereStartsWith('@') }}>
                            {{ $label }}
                        </div>
                    </div>
                @endif
            BLADE;
    }
}
