<?php

namespace Mary\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Tab extends Component
{
    public string $uuid;

    public function __construct(
        public ?string $id = null,
        public ?string $name = null,
        public ?string $label = null,
        public ?string $icon = null,
        public bool $disabled = false,
        public bool $hidden = false,
        public ?string $badge = null,
        public ?string $badgeClass = null,
    ) {
        $this->uuid = "mary" . md5(serialize($this)) . $id;
    }

    public function render(): View|Closure|string
    {
        return <<<'HTML'
                    @aware(['lift', 'box', 'labelClass', 'contentClass', 'activeClass'])

                    <div>
                        <label
                            @class([
                                "tab flex gap-3 flex-nowrap whitespace-nowrap px-5",
                                $labelClass,
                                "hidden" => $hidden,
                                "tab-disabled" => $disabled
                             ])
                            :class="{ '{{ $activeClass }}': selected === '{{ $name }}' }"
                        >
                            <input id="{{ ($id ?? $uuid).$name }}" type="radio" name="{{ ($id ?? $uuid).$name }}" value="{{ $name }}" x-model="selected" />

                            @if($icon)
                              <x-mary-icon :name="$icon"  />
                            @endif

                            {{ $label }}

                            @if ($badge)
                                <x-badge :value="$badge" @class(["badge-sm badge-primary", $badgeClass]) />
                            @endif
                        </label>
                        <div
                            x-show="selected == '{{ $name }}'"
                            {{ $attributes->class(["tab-content bg-base-100 py-5 px-3 border-t-base-300 block rounded-none", $contentClass, "px-5 border-base-300 rounded!" => $lift || $box]) }}
                         >
                            {{ $slot }}
                        </div>
                    </div>
                HTML;
    }
}
