<?php

namespace Mary\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Modal extends Component
{
    public function __construct(
        public string $id,
        public string $title = '',
        public string $subtitle = '',
        public bool $separator = false,
        public string $actions = ''
    ) {
        //
    }

    public function render(): View|Closure|string
    {
        return <<<'HTML'
                <dialog id="{{ $id }}" class="modal">
                    <div class="modal-box">
                        @if($title)
                            <x-header :title="$title" :subtitle="$subtitle" size="h3" :separator="$separator" />
                        @endif

                        <p class="pb-4">
                            {{ $slot }}
                        </p>
                        @if($separator) <hr /> @endif
                        <div class="modal-action">
                            {{ $actions }}
                        </div>
                    </div>
                </dialog>
                HTML;
    }
}
