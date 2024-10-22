<?php

namespace Mary\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Avatar extends Component
{
    public string $uuid;

    /**
     * @param  ?string  $image  The URL of the avatar image.
     * @param  ?string  $placeholder  The placeholder of the avatar.
     * @param  ?string  $title  The title text displayed beside the avatar.
     * @slot  ?string  $title  The title text displayed beside the avatar.
     * @param  ?string  $subtitle  The subtitle text displayed beside the avatar.
     * @slot  ?string  $subtitle The subtitle text displayed beside the avatar.
     */
    public function __construct(
        public ?string $image = '',
        public ?string $placeholder = '',

        // Slots
        public ?string $title = null,
        public ?string $subtitle = null

    ) {
        $this->uuid = "mary" . md5(serialize($this));
    }

    public function render(): View|Closure|string
    {
        return <<<'HTML'
            <div class="flex items-center gap-2">
                <div class="avatar @if(empty($image)) placeholder @endif">
                    <div {{ $attributes->class(["w-7 rounded-full", "bg-neutral text-neutral-content" => empty($image)]) }}>
                        @if(empty($image))
                            <span class="text-xs">{{ $placeholder }}</span>
                        @else
                            <img src="{{ $image }}" />
                        @endif
                    </div>
                </div>
                @if($title || $subtitle)
                <div>
                    @if($title)
                        <div @class(["font-semibold font-lg", is_string($title) ? '' : $title?->attributes->get('class') ]) >
                            {{ $title }}
                        </div>
                    @endif
                    @if($subtitle)
                        <div @class(["text-sm text-gray-400", is_string($subtitle) ? '' : $subtitle?->attributes->get('class') ]) >
                            {{ $subtitle }}
                        </div>
                    @endif
                </div>
                @endif
            </div>
            HTML;
    }
}
