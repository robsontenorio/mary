<?php

namespace Mary\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Illuminate\View\Component;

class Header extends Component
{
    public string $anchor = '';

    public function __construct(
        public ?string $title = null,
        public ?string $subtitle = null,
        public ?bool $separator = false,
        public ?string $progressIndicator = null,
        public ?bool $withAnchor = false,
        public ?string $size = 'text-4xl',

        // Slots
        public mixed $middle = null,
        public mixed $actions = null,
    ) {
        $this->anchor = Str::slug($title);
    }

    public function progressTarget(): ?string
    {
        if ($this->progressIndicator == 1) {
            return $this->attributes->whereStartsWith('progress-indicator')->first();
        }

        return $this->progressIndicator;
    }

    public function render(): View|Closure|string
    {
        return <<<'HTML'
                <div {{ $attributes->class(["mb-10"]) }}>
                    <div class="flex flex-wrap gap-5 justify-between items-center">
                        <div>
                            <div class="{{$size}} font-extrabold mary-header-anchor" id="{{ $anchor }}">
                                @if($withAnchor)
                                    <a href="#{{ $anchor }}">
                                @endif

                                {{ $title }}

                                @if($withAnchor)
                                    </a>
                                @endif
                            </div>

                            @if($subtitle)
                                <div class="text-gray-500 text-sm mt-1">{{ $subtitle }}</div>
                            @endif
                        </div>
                        <div class="flex items-center justify-center gap-3 grow order-last sm:order-none">
                            <div class="w-full lg:w-auto">
                                {{ $middle }}
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            {{ $actions}}
                        </div>
                    </div>

                    @if($separator)
                        <hr class="my-5" />

                        @if($progressIndicator)
                            <div class="h-0.5 -mt-9 mb-9">
                                <progress wire:loading @if($progressTarget()) wire:target="{{ $progressTarget() }}"  @endif class="progress w-full h-0.5"></progress>
                            </div>
                        @endif
                    @endif
                </div>
                HTML;
    }
}
