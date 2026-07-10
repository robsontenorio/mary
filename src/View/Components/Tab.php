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
                                "tab flex flex-nowrap items-center gap-3 whitespace-nowrap px-4",
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
                                <x-mary-badge :value="$badge" @class(["badge-sm badge-soft", $badgeClass]) />
                            @endif
                        </label>
                        <div
                            x-show="selected == '{{ $name }}'"
                            {{ $attributes->class(["tab-content py-5 px-3 border-t-base-content/10 block rounded-none", $contentClass]) }}
                         >
                            {{ $slot }}
                        </div>
                    </div>
                HTML;
    }
}
