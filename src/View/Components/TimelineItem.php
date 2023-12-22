<?php

namespace Mary\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class TimelineItem extends Component
{
    public string $uuid;

    public function __construct(
        public string $title,
        public ?string $subtitle = null,
        public ?string $description = null,
        public ?string $icon = null,
        public ?bool $pending = false,
        public ?bool $first = false,
        public ?bool $last = false,

    ) {
        $this->uuid = "mary" . md5(serialize($this));
    }

    public function render(): View|Closure|string
    {
        return <<<'HTML'
                <div>
                    <!-- Last item `border cut` -->
                    <div @class(["border-l-2 border-l-base-300 h-5 -mb-5" => $last, "!border-l-primary" => !$pending])>
                    </div>

                    <!-- WRAPPER THAT ALSO ACTS A LINE CONNECTOR -->
                    <div @class([
                            "border-l-2 border-l-base-300 pl-8 py-3",
                            "!border-l-primary" => !$pending,
                            "pt-0" => $first,
                            "!border-l-0" => $last,
                         ])
                    >
                        <!-- BULLET -->
                        <div @class([
                                "w-4 h-4 -mb-5 -ml-[41px] rounded-full bg-base-300",
                                "bg-primary" => !$pending,
                                "!-ml-[39px]" => $last,
                                "w-8 h-8 !-ml-[48px] -mb-7" => $icon
                             ])
                        >
                            <!-- ICON -->
                            @if($icon)
                                <x-mary-icon :name="$icon" @class(["ml-2 mt-1 w-4 h-4", "text-base-100" => !$pending ])  />
                            @endif
                        </div>

                        <!-- TITLE -->
                        <div @class(["font-bold mb-1"])>{{ $title }}</div>

                        <!-- SUBTITLE -->
                        @if($subtitle)
                            <div class="text-xs text-gray-500/50 font-bold">{{ $subtitle }}</div>
                        @endif

                        <!-- DESCRIPTION -->
                        @if($description)
                            <div class="text-sm mt-3">
                                {{ $description }}
                            </div>
                        @endif
                    </div>
                </div>
            HTML;
    }
}
