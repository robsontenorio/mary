<?php

namespace Mary\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Stat extends Component
{
    public string $uuid;

    public function __construct(
        public ?string $value = null,
        public ?string $icon = null,
        public ?string $color = 'text-primary',
        public ?string $title = null,
        public ?string $description = null,

    ) {
        $this->uuid = md5(serialize($this));
    }

    public function render(): View|Closure|string
    {
        return <<<'HTML'
                <div {{ $attributes->class(["bg-base-100 rounded-lg px-5 py-4  w-full"]) }} >
                    <div class="flex items-center gap-3">
                        @if($icon)
                            <div class="  {{ $color }}">
                                <x-icon :name="$icon" class="w-9 h-9" />
                            </div>
                        @endif

                        <div class="text-left">
                            @if($title)
                                <div class="text-xs text-gray-500 whitespace-nowrap">{{ $title }}</div>
                            @endif

                            <div class="font-black text-xl">{{ $value ?? $slot }}</div>

                            @if($description)
                                <div class="stat-desc">{{ $description }}</div>
                            @endif
                        </div>
                    </div>
                </div>
            HTML;
    }
}
