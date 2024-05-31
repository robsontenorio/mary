<?php

namespace Mary\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Collapse extends Component
{
    public string $uuid;

    public function __construct(
        public ?string $name = null,
        public ?bool $collapsePlusMinus = false,
        public ?bool $separator = false,
        public ?bool $noIcon = false,

        // Slots
        public mixed $heading = null,
        public mixed $content = null,
    ) {
        $this->uuid = "mary" . md5(serialize($this));
    }

    public function render(): View|Closure|string
    {
        return <<<'HTML'
                @aware(['noJoin' => null])

                <div
                    {{
                        $attributes->class([
                            'collapse border border-base-300',
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
                            {{ $heading->attributes->merge(["class" => "collapse-title text-xl font-medium"]) }}

                            @if(isset($noJoin))
                                :class="model == '{{ $name }}' && 'z-10'"
                                @click="if (model == '{{ $name }}') model = null"
                            @endif
                        >
                            {{ $heading }}
                        </div>
                        <div {{ $content->attributes->merge(["class" => "collapse-content"]) }} wire:key="content-{{ $uuid }}">
                            @if($separator)
                                <hr class="mb-3" />
                            @endif

                            {{ $content }}
                        </div>
                </div>
            HTML;
    }
}
