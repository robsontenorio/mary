<?php

namespace Mary\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Hr extends Component
{
    public string $uuid;

    public function __construct(
        public ?string $id = null,
        public ?string $target = null,
    ) {
        $this->uuid = "mary" . md5(serialize($this)) . $id;
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
                <div class="h-[2px] border-t-[length:var(--border)] border-t-base-content/10 my-5">
                    <progress
                        class="progress progress-primary hidden h-[1px]"
                        wire:loading.class="!h-[length:var(--border)] !block"

                        @if($progressTarget())
                            wire:target="{{ $progressTarget() }}"
                        @endif></progress>
                </div>
            HTML;
    }
}
