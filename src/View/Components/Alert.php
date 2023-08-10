<?php

namespace Mary\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Alert extends Component
{
    public string $uuid;

    public function __construct(
        public ?string $title = null,
        public ?string $icon = null,
        public ?string $description = null,
        public ?bool $shadow = false,

        // Slots
        public mixed $actions = null
    ) {
        $this->uuid = md5(serialize($this));
    }

    public function render(): View|Closure|string
    {
        return <<<'HTML'
                <div 
                    wire:key="{{ $uuid }}" 
                    {{ $attributes->whereDoesntStartWith('class') }} 
                    {{ $attributes->class(['alert rounded-md', 'shadow-md' => $shadow])}}
                >
                    @if($icon) 
                        <x-icon :name="$icon" /> 
                    @endif

                    @if($title)
                        <div>
                            <div class="font-bold">{{ $title }}</div>
                            <div class="text-xs">{{ $description }}</div>
                        </div>
                    @else
                        <span>{{ $slot }}</span>
                    @endif

                    <div class="flex items-center gap-3">
                        {{ $actions }}
                    </div>
                </div>
            HTML;
    }
}
