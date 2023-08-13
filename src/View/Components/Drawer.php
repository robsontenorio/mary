<?php

namespace Mary\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Drawer extends Component
{
    public string $uuid;

    public function __construct(
        public ?string $id = null,
        public ?bool $right = false
    ) {
        $this->uuid = md5(serialize($this));
    }

    public function render(): View|Closure|string
    {
        return <<<'HTML'
                @php 
                    $id = $id ?? $attributes?->whereStartsWith('wire:model')->first() 
                @endphp

                <div class="drawer absolute z-50 @if($right) drawer-end @endif">
                    <!-- Toggle visibility  -->
                    <input 
                        id="{{ $id }}" 
                        type="checkbox" 
                        class="drawer-toggle" 
                        {{ $attributes->whereStartsWith('wire:model') }} />

                    <div class="drawer-side">
                        <!-- Overlay effect , click outside -->                        
                        <label for="{{ $id }}" class="drawer-overlay"></label>

                        <!-- Content -->
                        <div {{ $attributes->class(['bg-base-100 min-h-screen']) }}>
                            {{ $slot }}
                        </div>
                    </div>
                </div>
            HTML;
    }
}
