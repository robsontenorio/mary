<?php

namespace Mary\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Alert extends Component
{
    public string $uuid;

    public function __construct(
        public string $title = '',
        public string $icon = '',
        public string $description = '',
        public bool $shadow = false,
        public mixed $actions = ''
    ) {
        $this->uuid = md5(serialize($this));
    }

    public function render(): View|Closure|string
    {
        return <<<'HTML'
                <div wire:key="{{ $uuid }}" {{ $attributes->whereDoesntStartWith('class') }} {{ $attributes->class(['alert rounded-md', 'shadow-md' => $shadow])}}>
                    @if($icon)
                        @svg($icon)                        
                    @endif
                    @if($title)
                    <div>
                        <div class="font-bold">{{ $title }}</div>
                        <div class="text-xs">{{ $description }}</div>
                    </div>
                    @else
                        <span>{{ $slot }}</span>
                    @endif
                    <div>
                        {{ $actions }}
                    </div>
                </div>
            HTML;
    }
}
