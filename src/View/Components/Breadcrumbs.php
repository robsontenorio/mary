<?php

namespace Mary\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use InvalidArgumentException;

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
     * @param  bool  $schema  Emit JSON-LD for at least two named breadcrumbs with absolute HTTP(S) links on non-final items.
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
        public bool $schema = false,
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

    public function schemaJson(): ?string
    {
        if (! $this->schema) {
            return null;
        }

        $items = [];
        $count = count($this->items);

        foreach ($this->items as $element) {
            $position = count($items) + 1;
            $label = $element['label'] ?? null;

            if (! is_string($label) || trim($label) === '') {
                throw new InvalidArgumentException('Breadcrumbs schema requires a non-empty label for every item.');
            }

            $item = ['@type' => 'ListItem', 'position' => $position, 'name' => $label];

            if ($position < $count) {
                $link = $element['link'] ?? null;

                if (! is_string($link) || ! filter_var($link, FILTER_VALIDATE_URL) || ! in_array(strtolower(parse_url($link, PHP_URL_SCHEME) ?? ''), ['http', 'https'], true)) {
                    throw new InvalidArgumentException('Breadcrumbs schema requires an absolute HTTP(S) link for every non-final item.');
                }

                $item['item'] = $link;
            }

            $items[] = $item;
        }

        // Google breadcrumb trails require at least two real items.
        if ($count < 2) {
            return null;
        }

        // Escape HTML-sensitive characters before embedding raw JSON in a script element.
        return json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $items,
        ], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_THROW_ON_ERROR);
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
                                <a href="{{ $element['link'] }}" @if(!$noWireNavigate) wire:navigate @endif @if($loop->last) aria-current="page" @endif @class([$linkItemClass])>
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
                            <li role="presentation" aria-hidden="true" class="contents">
                                <span class="sm:hidden">...</span>
                            </li>
                        @endif

                        {{-- Separator --}}
                        <li role="presentation" aria-hidden="true" class="contents">
                            <span @class([
                                    "hidden",
                                    "!block" => ($loop->first || $loop->remaining == 1) && $loop->count > 1,
                                    "sm:!block" => !$loop->last && $loop->count > 1
                                 ])
                            >
                                <x-mary-icon :name="$separator" @class([$separatorClass]) />
                            </span>
                        </li>
                    @endforeach
                </ul>
                @if($schema && ($jsonLd = $schemaJson()))
                    <script type="application/ld+json">{!! $jsonLd !!}</script>
                @endif
            BLADE;
    }
}
