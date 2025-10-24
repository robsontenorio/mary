<?php

namespace Mary\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Breadcrumbs extends Component
{
    public string $uuid;

    /**
     * @param  array  $items  The steps that should be displayed. Each element supports the keys 'label', 'link', 'icon' and 'tooltip'.
     * @param  string  $separator  Any supported icon name, 'o-slash' by default.
     * @param ?string  $linkItemClass  The classes that should be applied to each item with a link.
     * @param ?string  $textItemClass  The classes that should be applied to each item without a link.
     * @param ?string  $iconClass  The classes that should be applied to each items icon.
     * @param ?string  $separatorClass  The classes that should be applied to each separator.
     * @param ?bool  $noWireNavigate  If true, the component will not use wire:navigate on links.
     */
    public function __construct(
        public ?string $id = null,
        public array $items = [],
        public string $separator = 'o-chevron-right',
        public ?string $linkItemClass = "hover:underline text-sm",
        public ?string $textItemClass = "text-sm",
        public ?string $iconClass = "h-4 w-4",
        public ?string $separatorClass = "h-3 w-3 mx-1 text-base-content/40",
        public ?bool $noWireNavigate = false,
    ) {
        $this->uuid = "mary" . md5(serialize($this)) . $id;
    }

    public function tooltip(array $element): ?string
    {
        return $element['tooltip'] ?? $element['tooltip-left'] ?? $element['tooltip-right'] ?? $element['tooltip-bottom'] ?? $element['tooltip-top'] ?? null;
    }

    public function tooltipPosition(array $element): string
    {
        return match (true) {
            isset($element['tooltip-left']) => 'lg:tooltip-left',
            isset($element['tooltip-right']) => 'lg:tooltip-right',
            isset($element['tooltip-bottom']) => 'lg:tooltip-bottom',
            default => 'lg:tooltip-top',
        };
    }

    public function render(): View|Closure|string
    {
        return <<<'BLADE'
                <ul {{ $attributes->merge(['class' => 'flex items-center']) }} wire:key="{{ $uuid }}">
                    @foreach($items as $element)

                        {{-- Tooltip --}}
                        <li
                            @class(["lg:tooltip {$tooltipPosition($element)}" => $tooltip($element), "hidden sm:block" => !$loop->first && !$loop->last])

                            @if($tooltip($element))
                                data-tip="{{ $tooltip($element) }}"
                            @endif
                        >

                            @if ($element['link'] ?? null)
                                <a href="{{ $element['link'] }}" @if(!$noWireNavigate) wire:navigate @endif @class([$linkItemClass])>
                            @else
                                <span @class([$textItemClass])>
                            @endif

                                {{-- Icon --}}
                                @if($element['icon'] ?? null)
                                    <x-mary-icon :name="$element['icon']" @class(["mb-0.5", $iconClass]) />
                                @endif

                                {{-- Text --}}
                                <span>
                                    {{ $element['label'] ?? null }}
                                </span>

                            @if ($element['link'] ?? null)
                                </a>
                            @else
                                </span>
                            @endif
                        </li>

                        @if($loop->remaining == 1 && $loop->count > 2)
                            <span class="sm:hidden">...</span>
                        @endif

                        {{-- Separator --}}
                        <span @class([
                                "hidden",
                                "!block" => ($loop->first || $loop->remaining == 1) && $loop->count > 1,
                                "sm:!block" => !$loop->last && $loop->count > 1
                             ])
                        >
                            <x-mary-icon :name="$separator" @class([$separatorClass]) />
                        </span>
                    @endforeach
                </ul>
            BLADE;
    }
}
