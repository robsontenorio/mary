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
     * @param  ?string  $alt  The HTML `alt` attribute
     * @param  ?string  $placeholder  The placeholder of the avatar.
     * @param  ?string  $title  The title text displayed beside the avatar.
     * @slot  ?string  $title  The title text displayed beside the avatar.
     * @param  ?string  $subtitle  The subtitle text displayed beside the avatar.
     * @slot  ?string  $subtitle The subtitle text displayed beside the avatar.
     */
    public function __construct(
        public ?string $id = null,
        public ?string $image = '',
        public ?string $alt = '',
        public ?string $placeholder = '',
        public ?string $fallbackImage = null,

        // Slots
        public ?string $title = null,
        public ?string $subtitle = null

    ) {
        $this->uuid = "mary" . md5(serialize($this)) . $id;
    }

    public function render(): View|Closure|string
    {
        return <<<'BLADE'
            <div class="flex items-center gap-3">
                <div class="avatar @if(empty($image)) avatar-placeholder @endif">
                    <div {{ $attributes->class(["w-7 rounded-full", "bg-neutral text-neutral-content" => empty($image)]) }}>
                        @if(empty($image))
                            <span class="text-xs" alt="{{ $alt }}">{{ $placeholder }}</span>
                        @else
                            <img src="{{ $image }}" alt="{{ $alt }}" @if($fallbackImage) onerror="this.src='{{ $fallbackImage }}'" @endif />
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
                        <div @class(["text-sm text-base-content/50", is_string($subtitle) ? '' : $subtitle?->attributes->get('class') ]) >
                            {{ $subtitle }}
                        </div>
                    @endif
                </div>
                @endif
            </div>
            BLADE;
    }
}
