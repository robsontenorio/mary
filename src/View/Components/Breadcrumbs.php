<?php

namespace Mary\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Breadcrumbs extends Component
{
    public string $uuid;

    /**
     * @param array  $items  The steps that should be displayed. Each element supports the keys 'label', 'link', 'icon' and 'tooltip'.
     * @param string  $seperator  Any supported icon name, 'o-slash' by default.
     * @param ?string  $linkItemClass  The classes that should be applied to each item with a link.
     * @param ?string  $textItemClass  The classes that should be applied to each item without a link.
     * @param ?string  $iconClass  The classes that should be applied to each items icon.
     * @param ?string  $seperatorClass  The classes that should be applied to each seperator.
     */
    public function __construct(
        public ?string $id = null,
        public array $items = [],
        public string $seperator = 'o-slash',
        public ?string $linkItemClass = "btn btn-ghost btn-sm",
        public ?string $textItemClass = "btn btn-ghost btn-sm pointer-events-none",
        public ?string $iconClass = "h-5 w-5",
        public ?string $seperatorClass = "h-4 w-4",
    ) {
        $this->uuid = "mary" . md5(serialize($this)) . $id;
    }

    public function render(): View|Closure|string
    {
        return <<<'BLADE'
                <ul {{ $attributes->merge(['class' => 'flex items-center']) }} wire:key="{{ $uuid }}">
                    @foreach($items as $element)
                        
                        {{-- Tooltip --}}
                        @php
                            $tooltip = $element['tooltip'] ?? $element['tooltip-left'] ?? $element['tooltip-right'] ?? $element['tooltip-bottom'] ?? $element['tooltip-top'] ?? null;
                            $tooltipPosition = array_key_exists('tooltip-left', $element) ? 'lg:tooltip-left' : (array_key_exists('tooltip-right', $element) ? 'lg:tooltip-right' : (array_key_exists('tooltip-bottom', $element) ? 'lg:tooltip-bottom' : 'lg:tooltip-top'));
                        @endphp
                        <li @class(["!inline-flex lg:tooltip $tooltipPosition" => $tooltip]) 
                            @if($tooltip)
                                data-tip="{{ $tooltip }}"
                            @endif
                        >

                            @if ($element['link'] ?? null)
                                <a href="{{ $element['link'] }}" @class([$linkItemClass])>
                            @else
                                <span @class([$textItemClass])>
                            @endif

                                {{-- Icon --}}
                                @if($element['icon'] ?? null)
                                    <x-icon :name="$element['icon']" @class([$iconClass]) />
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
                        
                        {{-- Seperator --}}
                        @if (!$loop->last)
                            <x-icon name="{{ $seperator }}" @class([$seperatorClass]) />
                        @endif

                    @endforeach
                </ul>
            BLADE;
    }
}
