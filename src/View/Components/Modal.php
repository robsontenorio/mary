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
        public ?string $boxClass = null,
        public ?bool $separator = false,
        public ?bool $persistent = false,
        public ?bool $withoutTrapFocus = false,

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
                        @if(!$persistent)
                            @keydown.escape.window = "$wire.{{ $attributes->wire('model')->value() }} = false"
                        @endif
                    @endif

                    @if(!$withoutTrapFocus)
                        x-trap="open" x-bind:inert="!open"
                    @endif
                >
                    <div class="modal-box {{ $boxClass }}">
                        @if(!$persistent)
                            <form method="dialog">
                                @if ($id)
                                    <button class="btn btn-sm btn-circle border-0 shadow-none absolute end-2 top-2 font-bold text-xl z-[999] bg-base-100" type="submit">✕</button>
                                @else
                                    <button class="btn btn-sm btn-circle border-0 shadow-none absolute end-2 top-2 font-bold text-xl z-[999] bg-base-100" @click="$wire.{{ $attributes->wire('model')->value() }} = false" type="button">✕</button>
                                @endif
                            </form>
                        @endif
                        @if($title)
                            <x-mary-header :title="$title" :subtitle="$subtitle" size="text-2xl" :separator="$separator" class="mb-5" />
                        @endif

                        <p class="">
                            {{ $slot }}
                        </p>

                        @if($separator)
                            <hr class="mt-5" />
                        @endif

                        @if($actions)
                            <div class="modal-action">
                                {{ $actions }}
                            </div>
                        @endif
                    </div>

                    @if(!$persistent)
                        <form class="modal-backdrop" method="dialog">
                            @if ($id)
                                <button type="submit">close</button>
                            @else
                                <button @click="$wire.{{ $attributes->wire('model')->value() }} = false" type="button">close</button>
                            @endif
                        </form>
                    @endif
                </dialog>
                HTML;
    }
}
