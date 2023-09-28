<?php

namespace Mary\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Modal extends Component
{
    public function __construct(
        public ?string $id = '',
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
                <dialog 
                    {{ $attributes->except('wire:model')->class(["modal"]) }}
                    
                    @if($id)
                        id="{{ $id }}"
                    @else
                        x-data="{open: @entangle($attributes->wire('model')).live }"                         
                        :class="{'modal-open !animate-none': open}"
                        :open="open"
                        @keydown.escape.window = "$wire.{{ $attributes->wire('model')->value() }} = false"
                    @endif
                >
                    <div class="modal-box">
                        @if($title)
                            <x-header :title="$title" :subtitle="$subtitle" size="text-2xl" :separator="$separator" class="mb-5" />
                        @endif

                        <p class="">
                            {{ $slot }}
                        </p>

                        @if($separator) 
                            <hr class="mt-5" /> 
                        @endif

                        <div class="modal-action">
                            {{ $actions }}
                        </div>
                    </div>
                </dialog>
                HTML;
    }
}
