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
                    x-data="{
                        show: false,
                        toast: {},
                        timer: null,
                        interval: null,

                        maxProgress: 100,
                        progress: 100,
                        initialProgress: 0,

                        startTime: 0,
                        remaining: 0,

                        start(toast) {
                            this.clearTimers();

                            this.toast = toast;
                            this.progress = this.maxProgress;

                            this.remaining = toast.timeout;

                            this.initialProgress = this.progress;
                            this.startTime = Date.now();

                            // delay for DOM initiation
                            setTimeout(() => this.show = true, 50);

                            this.startProgress();
                            this.startCloseTimer();
                        },

                        startProgress() {
                            if (this.toast.noProgress) return;

                            const intervalRefreshRate = 8;
                            this.startTime = Date.now();

                            const startProgressValue = this.initialProgress;

                            this.interval = setInterval(() => {
                                const elapsed = Date.now() - this.startTime;
                                const ratio = elapsed / this.remaining;

                                this.progress = startProgressValue * (1 - ratio);

                                if (this.progress <= 0) {
                                    this.progress = 0;
                                    clearInterval(this.interval);
                                    this.interval = null;
                                }
                            }, intervalRefreshRate);
                        },

                        startCloseTimer() {
                            this.startTime = Date.now();

                            this.timer = setTimeout(() => {
                                this.close();
                            }, this.remaining);
                        },

                        pause() {
                            if (!this.show) return;

                            const elapsed = Date.now() - this.startTime;
                            this.remaining -= elapsed;

                            this.initialProgress = this.progress;

                            this.clearTimers();
                        },

                        resume() {
                            if (!this.show || this.remaining <= 0) return;

                            this.startProgress();
                            this.startCloseTimer();
                        },

                        close() {
                            this.show = false;
                            this.clearTimers();
                        },

                        clearTimers() {
                            clearTimeout(this.timer);
                            clearInterval(this.interval);
                            this.timer = null;
                            this.interval = null;
                        }
                    }"
                    @mary-toast.window="start($event.detail.toast)"
                >
                    <div
                        class="toast !whitespace-normal rounded-md fixed cursor-pointer z-[999] overflow-hidden"
                        :class="toast.position || '{{ $position }}'"
                        x-show="show"
                        @mouseenter="pause()"
                        @mouseleave="resume()"
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
                            x-show="!toast.noProgress"
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
