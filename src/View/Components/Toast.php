<?php

namespace Mary\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Toast extends Component
{
    public function __construct(
        public string $position = 'toast-top toast-end'
    ) {
    }

    public function render(): View|Closure|string
    {
        return <<<'HTML'
            <div>
                @persist('mary-toaster')
                <div
                    x-cloak
                    x-data="{ show: false, timer: '', toast: ''}"
                    @mary-toast.window="
                                    clearTimeout(timer);
                                    toast = $event.detail.toast
                                    setTimeout(() => show = true, 100);
                                    timer = setTimeout(() => show = false, $event.detail.toast.timeout);
                                    "
                >
                    <div
                        class="toast rounded-md fixed cursor-pointer z-50"
                        :class="toast.position || '{{ $position }}'"
                        x-show="show"
                        x-classes="alert alert-success alert-warning alert-error alert-info top-10 right-10 toast toast-top toast-bottom toast-center toast-end toast-middle toast-start"
                        @click="show = false"
                    >
                        <div class="alert gap-2" :class="toast.css">
                            <div x-html="toast.icon"></div>
                            <div class="grid">
                                <div x-html="toast.title" class="font-bold"></div>
                                <div x-html="toast.description" class="text-xs"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <script>
                    window.toast = function(payload){
                        window.dispatchEvent(new CustomEvent('mary-toast', {detail: payload}))
                    }

                    document.addEventListener('livewire:init', () => {
                        Livewire.hook('request', ({fail}) => {
                            fail(({status, content, preventDefault}) => {
                                try {
                                    let result = JSON.parse(content);

                                    if (result?.toast && typeof window.toast === "function") {
                                        window.toast(result);
                                    }

                                    if ((result?.prevent_default ?? false) === true) {
                                        preventDefault();
                                    }
                                } catch (e) {
                                    console.log(e)
                                }
                            })
                        })
                    })
                </script>
                @endpersist
            </div>
            HTML;
    }
}
