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
            <nav aria-label="Breadcrumb">
                <ol class="flex items-center" itemscope itemtype="https://schema.org/BreadcrumbList" wire:key="{{ $uuid }}">
                    @foreach($items as $index => $element)
                        <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem"
                            @class([
                                "lg:tooltip {$tooltipPosition($element)}" => $tooltip($element),
                                "hidden sm:block" => !$loop->first && !$loop->last
                            ])
                            @if($tooltip($element))
                                data-tip="{{ $tooltip($element) }}"
                            @endif
                        >

                            @if ($element['link'] ?? null)
                                <a itemprop="item" href="{{ $element['link'] }}" @if(!$noWireNavigate) wire:navigate @endif @class([$linkItemClass])>
                            @else
                                <span itemprop="item" @class([$textItemClass]) @if($loop->last) aria-current="page" @endif>
                            @endif

                                {{-- Icon --}}
                                @if($element['icon'] ?? null)
                                    <x-mary-icon :name="$element['icon']" @class(["mb-0.5", $iconClass]) />
                                @endif

                                <span itemprop="name">
                                    {{ $element['label'] ?? null }}
                                </span>

                            @if ($element['link'] ?? null)
                                </a>
                            @else
                                </span>
                            @endif

                            <meta itemprop="position" content="{{ $index + 1 }}" />
                        </li>

                        @if(!$loop->last)
                            <li aria-hidden="true" @class([
                                "flex items-center flex-shrink-0",
                                "hidden sm:flex" => !$loop->first
                            ])>
                                <x-mary-icon :name="$separator" @class([$separatorClass]) />
                            </li>
                        @endif

                        @if($loop->remaining == 1 && $loop->count > 2)
                            <li aria-hidden="true" class="sm:hidden flex items-center flex-shrink-0">
                                <span class="mx-1">...</span>
                            </li>
                        @endif
                    @endforeach
                </ol>
            </nav>
        BLADE;
    }
}
