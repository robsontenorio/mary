<?php

namespace Mary\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class ProgressRadial extends Component
{
    public string $uuid;

    public function __construct(
        public ?string $id = null,
        public ?float $value = 0,
        public ?string $unit = '%'
    ) {
        $this->uuid = "mary" . md5(serialize($this)) . $id;
    }

    public function render(): View|Closure|string
    {
        return <<<'HTML'
                 <div
                    {{
                        $attributes
                            ->class("radial-progress")
                            ->style("--value: $value")
                    }}

                    role="progressbar"
                >
                    {{ $value }}{{ $unit }}
                </div>
            HTML;
    }
}
