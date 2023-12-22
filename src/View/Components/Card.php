<?php

namespace Mary\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Card extends Component
{
    public string $uuid;

    public function __construct(
        public ?string $title = null,
        public ?string $subtitle = null,
        public ?bool $separator = false,
        public ?bool $shadow = false,
        public ?string $progressIndicator = null,

        // Slots
        public mixed $menu = null,
        public mixed $actions = null,
        public mixed $figure = null,
    ) {
        $this->uuid = "mary" . md5(serialize($this));
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
                <div
                    {{
                        $attributes
                            ->merge(['wire:key' => $uuid ])
                            ->class(['card bg-base-100 rounded-lg p-5', 'shadow-sm' => $shadow])
                    }}
                >
                    @if($figure)
                        <figure {{ $figure->attributes->class(["mb-5 -m-5"]) }}>
                            {{ $figure }}
                        </figure>
                    @endif

                    @if($title || $subtitle)
                        <div class="pb-5">
                            <div class="flex justify-between items-center">
                                <div>
                                    @if($title)
                                        <div @class(["text-2xl font-bold", is_string($title) ? '' : $title?->attributes->get('class') ]) >
                                            {{ $title }}
                                        </div>
                                    @endif
                                    @if($subtitle)
                                    <div @class(["text-gray-500 text-sm mt-1", is_string($subtitle) ? '' : $subtitle?->attributes->get('class') ]) >
                                            {{ $subtitle }}
                                        </div>
                                    @endif
                                </div>

                                @if($menu)
                                    <div {{ $menu->attributes->class(["flex items-center gap-2"]) }}> {{ $menu }} </div>
                                @endif
                            </div>

                            @if($separator)
                                <hr class="mt-3" />

                                @if($progressIndicator)
                                    <div class="h-0.5 -mt-4 mb-4">
                                        <progress
                                            class="progress progress-primary w-full h-0.5 dark:h-1"
                                            wire:loading

                                            @if($progressTarget())
                                                wire:target="{{ $progressTarget() }}"
                                             @endif></progress>
                                    </div>
                                @endif
                            @endif
                        </div>
                    @endif

                    <div>
                        {{ $slot }}
                    </div>

                    @if($actions)
                        @if($separator)
                            <hr class="mt-5" />
                        @endif

                        <div class="flex justify-end gap-3 p-3">
                            {{ $actions }}
                        </div>
                    @endif
                </div>
            HTML;
    }
}
