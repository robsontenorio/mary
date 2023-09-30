<?php

namespace Mary\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Illuminate\View\Component;

class Tab extends Component
{
    public string $uuid;

    public function __construct(
        public ?string $name = null,
        public ?string $label = null,
        public ?string $icon = null
    ) {
        $this->uuid = Str::uuid();
    }

    public function render(): View|Closure|string
    {
        return <<<'HTML'
                    @aware(['tabContainer' =>  '', 'selected' => ''])
                    <a
                        @click="
                            @if($selected)
                                selected = '{{ $name }}'
                            @else
                                $wire.selectedTab = '{{ $name }}'
                            @endif
                            "
                        :class="{ 'tab-active': selected === '{{ $name }}' }"
                        {{ $attributes->whereDoesntStartWith('class') }}
                        {{ $attributes->class(['tab tab-bordered flex-none font-semibold']) }}>

                        @if($icon)
                            <x-icon :name="$icon" class="mr-2" />
                        @endif

                        {{ $label }}
                    </a>

                    <div wire:key="{{ $name }}-{{ rand() }}">
                        <template x-teleport="#{{ $tabContainer }}">
                            <div class="py-5" x-show="selected === '{{ $name }}'">
                                {{ $slot }}
                            </div>
                        </template>
                    </div>
                HTML;
    }
}
