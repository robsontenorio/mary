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
        public ?bool $checked = false,
        public ?bool $collapseArrow = false,
        public ?bool $collapsePlusMinus = false,

        // Slots
        public mixed $heading = null,
        public mixed $content = null,
    public mixed $actions = null
    ) {
        $this->uuid = "mary" . md5(serialize($this));
    }

    public function render(): View|Closure|string
    {
        return <<<'HTML'
                <div
                    {{ $attributes->merge(['class' => 'collapse bg-base-200']) }}
                    :class="{'join-item': join, 'collapse-arrow': '{{ $collapseArrow }}', 'collapse-plus': '{{ $collapsePlusMinus }}'}"
                    wire:key="{{ $uuid }}">
                        <input type="radio" name="{{ $name }}" {{ $checked ? 'checked="checked"' : '' }}/>
                        <div {{ $heading->attributes->merge(["class" => "collapse-title text-xl font-medium"])  }}>
                            {{ $heading }}
                        </div>
                        <div {{ $content->attributes->merge(["class" => "collapse-content"])  }}>
                            {{ $content }}
                        </div>
                </div>
            HTML;
    }
}
