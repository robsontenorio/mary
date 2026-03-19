<?php

namespace Mary\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Collapse extends Component
{
    public string $uuid;

    public function __construct(
        public ?string $id = null,
        public ?string $name = null,
        public ?bool $collapsePlusMinus = false,
        public ?bool $separator = false,
        public ?string $progressIndicator = null,
        public ?bool $noIcon = false,

        // Slots
        public mixed $heading = null,
        public mixed $content = null,
    ) {
        $this->uuid = "mary" . md5(serialize($this)) . $id;
    }

    public function progressTarget(): ?string
    {
        if ($this->progressIndicator == 1) {
            return $this->attributes->whereStartsWith('progress-indicator')->first();
        }

        return $this->progressIndicator;
    }

    public function render(): View|Closure|string
    {
        return <<<'HTML'
                @aware(['noJoin' => null])

                <div
                    {{
                        $attributes->class([
                            'collapse border-[length:var(--border)] border-base-content/10',
                            'join-item' => !$noJoin,
                            'collapse-arrow' => !$collapsePlusMinus && !$noIcon,
                            'collapse-plus' => $collapsePlusMinus && !$noIcon
                        ])
                    }}

                    wire:key="collapse-{{ $uuid }}"
                >
                        <!-- Detects if it is inside an accordion.  -->
                        @if(isset($noJoin))
                            <input id="radio-{{ $uuid }}" type="radio" value="{{ $name }}" x-model="model" />
                        @else
                            <input id="checkbox-{{ $uuid }}" {{ $attributes->wire('model') }} type="checkbox" />
                        @endif

                        <div
                            {{ $heading->attributes->merge(["class" => "collapse-title font-semibold"]) }}

                            @if(isset($noJoin))
                                :class="model == '{{ $name }}' && 'z-10'"
                                @click="if (model == '{{ $name }}') model = null"
                            @endif
                        >
                            {{ $heading }}
                        </div>
                        <div {{ $content->attributes->merge(["class" => "collapse-content text-sm"]) }} wire:key="content-{{ $uuid }}">
                            @if($separator)
                                <hr class="mb-3 border-t-[length:var(--border)] border-base-content/10" />

                                @if($progressIndicator)
                                    <div class="h-0.5 -mt-6.5 mb-6.5">
                                        <progress
                                            class="progress progress-primary w-full h-0.5"
                                            wire:loading

                                            @if($progressTarget())
                                                wire:target="{{ $progressTarget() }}"
                                             @endif></progress>
                                    </div>
                                @endif
                            @endif

                            {{ $content }}
                        </div>
                </div>
            HTML;
    }
}
