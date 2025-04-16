<?php

namespace Mary\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Blade;
use Illuminate\View\Component;

class Step extends Component
{
    public string $uuid;

    public function __construct(
        public int $step,
        public string $text,
        public ?string $id = null,
        public ?string $icon = null,
        public ?string $stepClasses = null,
        public ?string $dataContent = null,

    ) {
        $this->uuid = "mary" . md5(serialize($this)) . $id;
    }

    public function iconHTML(): ?string
    {
        return Blade::render("<x-mary-icon name='" . $this->icon . "' class='w-4 w-4' />");
    }

    public function render(): View|Closure|string
    {
        return <<<'BLADE'
                    <div
                        class="hidden"
                        x-init="steps.push({ step: '{{ $step }}', text: '{{ $text }}', classes: '{{ $stepClasses }}' @if($icon) , icon: {{ json_encode($iconHTML()) }}  @endif @if($dataContent), dataContent: '{{ $dataContent }}' @endif })"
                    ></div>

                    <div x-show="current == '{{ $step }}'" {{ $attributes->class("px-1") }} >
                        {{ $slot }}
                    </div>
            BLADE;
    }
}
