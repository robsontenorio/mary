<?php

namespace Mary\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Swap extends Component
{
    public string $uuid;

    public function __construct(
        public ?string $id = null,
        public ?string $true = null,
        public ?string $false = null,
        public ?string $trueIcon = 'o-sun',
        public ?string $falseIcon = 'o-moon',
        public ?string $iconSize = "h-5 w-5",
    ) {
        $this->uuid = "mary" . md5(serialize($this)) . $id;
    }

    public function render(): View|Closure|string
    {
        return <<<'BLADE'
                <label
                    for="{{ $uuid }}"
                    {{ $attributes->whereDoesntStartWith('wire:model') }}>

                    {{-- Before --}}
                    @isset ($before)
                        <div {{ $before->attributes }}>
                            {{ $before }}
                        </div>
                    @endif

                    <div class="swap">

                        {{-- Hidden checkbox for state --}}
                        <input id="{{ $uuid }}" type="checkbox" {{ $attributes->wire('model') }} />

                        {{-- True Element --}}
                        @isset ($true)
                            <div {{ is_string($true) ? new Illuminate\View\ComponentAttributeBag(['class' => 'swap-on']) : $true->attributes->merge(['class' => 'swap-on']) }}>
                                {{ $true ?? '' }}
                            </div>
                        @else
                            <x-mary-icon :name="$trueIcon" class="swap-on {{ $iconSize }}" />
                        @endif

                        {{-- False Element --}}
                        @isset ($false)
                        <div {{ is_string($false) ? new Illuminate\View\ComponentAttributeBag(['class' => 'swap-off']) : $false->attributes->merge(['class' => 'swap-off']) }}>
                                {{ $false ?? '' }}
                            </div>
                        @else 
                            <x-mary-icon :name="$falseIcon" class="swap-off {{ $iconSize }}" />
                        @endif

                    </div>

                    {{-- After --}}
                    @isset ($after)
                        <div {{ $after->attributes }}>
                            {{ $after }}
                        </div>
                    @endif
                </label>
            BLADE;
    }
}
