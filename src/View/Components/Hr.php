<?php

namespace Mary\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Hr extends Component
{
    public string $uuid;

    public function __construct(
        public ?string $target = null,
    ) {
        $this->uuid = "mary" . md5(serialize($this));
    }

    public function progressTarget(): ?string
    {
        if ($this->target == 1) {
            return $this->attributes->whereStartsWith('target')->first();
        }

        return $this->target;
    }

    public function render(): View|Closure|string
    {
        return <<<'HTML'
                <div class="h-[2px] border border-t-base-100 my-5">
                    <progress
                        class="progress progress-primary hidden h-[1px]"
                        wire:loading.class="!h-[2px] !block"

                        @if($progressTarget())
                            wire:target="{{ $progressTarget() }}"
                         @endif></progress>
                </div>
            HTML;
    }
}
