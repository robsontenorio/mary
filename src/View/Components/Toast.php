<?php

namespace Mary\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Toast extends Component
{
    public function __construct()
    {
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
                        :class="toast.position"
                        x-show="show"
                        x-transition.opacity.scale
                        x-transition:enter.duration.700ms
                        @click="show = false"
                        >
                        <div class="alert" :class="toast.css">
                            <div class="grid">
                                <div x-text="toast.title" class="font-bold"></div>
                                <div x-text="toast.description" class="text-xs"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Force Tailwind compile alert types -->
                <span class="hidden alert alert-success alert-warning alert-error alert-info top-10 right-10 toast toast-top toast-bottom toast-center toast-end toast-middle toast-start"></span>

                <script>
                    window.toast = function(payload){
                        window.dispatchEvent(new CustomEvent('mary-toast', {detail: payload}))
                    }
                </script>
                @endpersist
            </div>
            HTML;
    }
}
