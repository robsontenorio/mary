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

        // Slots
        public mixed $menu = null,
        public mixed $actions = null,
        public mixed $figure = null,
    ) {
        $this->uuid = md5(serialize($this));
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
                    <figure>
                        {{ $figure }}
                    </figure>

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
