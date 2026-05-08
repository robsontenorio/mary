<?php

namespace Mary\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Card extends Component
{
    public string $uuid;

    public function __construct(
        public ?string $id = null,
        public ?string $title = null,
        public ?string $subtitle = null,
        public ?bool $separator = false,
        public ?bool $shadow = false,
        public ?string $progressIndicator = null,

        // Slots
        public mixed $menu = null,
        public mixed $actions = null,
        public mixed $figure = null,
        public ?string $bodyClass = 'null'
    ) {
        $this->uuid = "mary" . md5(serialize($this)) . $id;
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
                            ->class(['card bg-base-100 p-5', 'shadow-xs' => $shadow])
                    }}
                >
                    @if($figure)
                        <figure {{ $figure->attributes->class(["mb-5 -m-5"]) }}>
                            {{ $figure }}
                        </figure>
                    @endif

                    @if($title || $subtitle)
                        <div class="pb-5">
                            <div class="flex gap-3 justify-between items-center w-full">
                                <div class="grow-1">
                                    @if($title)
                                        <div @class(["text-xl font-bold", is_string($title) ? '' : $title?->attributes->get('class') ]) >
                                            {{ $title }}
                                        </div>
                                    @endif
                                    @if($subtitle)
                                    <div @class(["text-base-content/50 text-sm mt-1", is_string($subtitle) ? '' : $subtitle?->attributes->get('class') ]) >
                                            {{ $subtitle }}
                                        </div>
                                    @endif
                                </div>

                                @if($menu)
                                    <div {{ $menu->attributes->class(["flex items-center gap-2"]) }}> {{ $menu }} </div>
                                @endif
                            </div>

                            @if($separator)
                                <hr class="mt-3 border-t-[length:var(--border)] border-base-content/10" />

                                @if($progressIndicator)
                                    <div class="h-0.5 -mt-4 mb-4">
                                        <progress
                                            class="progress progress-primary w-full h-0.5"
                                            wire:loading

                                            @if($progressTarget())
                                                wire:target="{{ $progressTarget() }}"
                                             @endif></progress>
                                    </div>
                                @endif
                            @endif
                        </div>
                    @endif

                    <div @class([ 'grow-1', $bodyClass ])>
                        {{ $slot }}
                    </div>

                    @if($actions)
                        @if($separator)
                            <hr class="mt-5 border-t-[length:var(--border)] border-base-content/10" />
                        @else
                            <div></div>
                        @endif

                        <div @class(["flex w-full items-end justify-end gap-3 pt-5", is_string($actions) ? '' : $actions?->attributes->get('class') ])>
                            {{ $actions }}
                        </div>
                    @endif
                </div>
            HTML;
    }
}
