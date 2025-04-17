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
        public ?string $size = 'text-2xl',
        public ?string $icon = null,
        public ?string $iconClasses = null,
        public ?bool $sidebarToggler = false,
        public ?bool $sidebarTogglerIcon = false,
        public ?bool $sidebarTogglerIconClasses = false,

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
                <div id="{{ $anchor }}" {{ $attributes->class(["mb-10", "mary-header-anchor" => $withAnchor]) }}>
                    <div class="flex flex-wrap gap-5 justify-between items-center">
                        <div>
                            <div @class(["flex", "items-center", "$size font-extrabold", is_string($title) ? '' : $title?->attributes->get('class') ]) >
                                @if($withAnchor)
                                    <a href="#{{ $anchor }}">
                                @endif

                                @if($sidebarToggler)
                                    <div class="hidden sm:inline-flex items-center">
                                        <x-icon name="lucide.panel-left" class="hover:text-base-content/80 text-base-content/60 cursor-pointer" @click="$dispatch('mary-sidebar-toggle')" />
                                        <div class="border-r border-r-2 border-r-base-content/10 h-4 ms-3 me-4">&nbsp;</div>
                                    </div>
                                @endif

                                @if($icon)
                                    <x-icon name="{{ $icon }}" class="{{ $iconClasses }}" />
                                @endif

                                <span @class(["ml-2" => $icon])>{{ $title }}</span>

                                @if($withAnchor)
                                    </a>
                                @endif
                            </div>

                            @if($subtitle)
                                <div @class(["text-base-content/50 text-sm mt-1", is_string($subtitle) ? '' : $subtitle?->attributes->get('class') ]) >
                                    {{ $subtitle }}
                                </div>
                            @endif
                        </div>

                        @if($middle)
                            <div @class(["flex items-center justify-center gap-3 grow order-last sm:order-none", is_string($middle) ? '' : $middle?->attributes->get('class')])>
                                <div class="w-full lg:w-auto">
                                    {{ $middle }}
                                </div>
                            </div>
                        @endif

                        <div @class(["flex items-center gap-3", is_string($actions) ? '' : $actions?->attributes->get('class') ]) >
                            {{ $actions}}
                        </div>
                    </div>

                    @if($separator)
                        <hr class="border-t-[length:var(--border)] border-base-content/10 mt-3" />

                        @if($progressIndicator)
                            <div class="h-0.5 -mt-4 mb-4">
                                <progress
                                    class="progress progress-primary w-full h-[var(--border)]"
                                    wire:loading

                                    @if($progressTarget())
                                        wire:target="{{ $progressTarget() }}"
                                     @endif></progress>
                            </div>
                        @endif
                    @endif
                </div>
                HTML;
    }
}
