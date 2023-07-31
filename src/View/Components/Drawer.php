<?php

namespace Mary\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Drawer extends Component
{
    public string $uuid;

    public function __construct(
        public string $id,
        public bool $right = false
    ) {
        $this->uuid = md5(serialize($this));
    }

    public function render(): View|Closure|string
    {
        return <<<'HTML'
                <div class="drawer absolute z-50 @if($right) drawer-end @endif">
                    <!-- Toggle visibility  -->
                    <input id="{{ $id }}" type="checkbox" class="drawer-toggle" {{ $attributes->whereStartsWith('wire:model') }} />

                    <div class="drawer-side">
                        <!-- Overlay effect , click outside -->                        
                        <label for="{{ $id }}" class="drawer-overlay"></label>

                        <!-- Content -->
                        <div {{ $attributes->class(['bg-base-100 h-full w-80']) }}>
                            {{ $slot }}
                        </div>
                    </div>
                </div>
            HTML;
    }
}
