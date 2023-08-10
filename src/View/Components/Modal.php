<?php

namespace Mary\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Modal extends Component
{
    public function __construct(
        public string $id,
        public ?string $title = null,
        public ?string $subtitle = null,
        public ?bool $separator = false,

        // Slots
        public ?string $actions = null
    ) {
        //
    }

    public function render(): View|Closure|string
    {
        return <<<'HTML'
                <dialog id="{{ $id }}" {{ $attributes->class(["modal"]) }}>
                    <div class="modal-box">
                        @if($title)
                            <x-header :title="$title" :subtitle="$subtitle" size="text-2xl" :separator="$separator" />
                        @endif

                        <p class="">
                            {{ $slot }}
                        </p>

                        @if($separator) 
                            <hr /> 
                        @endif

                        <div class="modal-action">
                            {{ $actions }}
                        </div>
                    </div>
                </dialog>
                HTML;
    }
}
