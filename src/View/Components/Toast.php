<?php

namespace Mary\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Toast extends Component
{
    public function __construct(
        public string $position = 'toast-top toast-end',
    ) {
    }

    public function render(): View|Closure|string
    {
        return <<<'HTML'
            <div>
                @persist('mary-toaster')
                <div
                    x-cloak
                    x-data="{ show: false, timer: '', toast: '', maxProgress: 100, progress: 100, interval: null }"
                    @mary-toast.window="
                        clearTimeout(timer);
                        clearInterval(interval);
                        
                        progress = maxProgress;
                        
                        toast = $event.detail.toast;
                        setTimeout(() => show = true, 100);
                        
                        const duration = toast.timeout;
                        const intervalRefreshRate = 8; // high refresh rate to avoid jerky progression
                        
                        if(toast.progress) {
                            const step = maxProgress / (duration / intervalRefreshRate);
                            interval = setInterval(() => {
                                progress = progress - step;
                                if (progress <= 0) {
                                    progress = 0;
                                    clearInterval(interval);
                                }
                            }, intervalRefreshRate);
                        }
                        
                        timer = setTimeout(() => {
                            show = false;
                        }, duration + 150); // 150 is for compensating the show delay to sync the progress with the toast timeout
                        "
                >
                    <div
                        class="toast !whitespace-normal rounded-md fixed cursor-pointer z-[999] overflow-hidden"
                        :class="toast.position || '{{ $position }}'"
                        x-show="show"
                        x-classes="alert alert-success alert-warning alert-error alert-info top-10 end-10 toast toast-top toast-bottom toast-center toast-end toast-middle toast-start"
                        @click="show = false; clearInterval(interval)"
                    >
                        <div class="alert gap-2" :class="toast.css">
                            <div x-html="toast.icon" class="hidden sm:inline-block"></div>
                            <div class="grid">
                                <div x-html="toast.title" class="font-bold"></div>
                                <div x-html="toast.description" class="text-xs"></div>
                            </div>
                        </div>
                        <progress
                            x-show="toast.progress"
                            class="-mt-3 h-1 w-full progress"
                            :class="toast.progressClass"
                            :max="maxProgress"
                            :value="progress">
                        </progress>
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
