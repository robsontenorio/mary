<?php

namespace Mary\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Dialog extends Component
{
    public string $uuid;

    public function __construct(
        public ?string $position = 'center',
        public ?bool $showBackdrop = true,
        public ?bool $blurBackdrop = false
    ) {
        $this->uuid = "mary" . md5(serialize($this));
    }

    public function render(): View|Closure|string
    {
        return <<<'HTML'
            <div>
                <div 
                    x-cloak
                    x-data="{
                        show: false,
                        title: '',
                        description: '',
                        icon: null,
                        css: 'dialog-info',
                        position: '{{ $position }}',
                        backdrop: {{ $showBackdrop ? 'true' : 'false' }},
                        blur: {{ $blurBackdrop ? 'true' : 'false' }},
                        confirmOptions: null,
                        cancelOptions: null,

                        getPositionClasses() {
                            const positions = {
                                'top-left': 'items-start justify-start',
                                'top': 'items-start justify-center',
                                'top-right': 'items-start justify-end',
                                'center-left': 'items-center justify-start',
                                'center': 'items-center justify-center',
                                'center-right': 'items-center justify-end',
                                'bottom-left': 'items-end justify-start',
                                'bottom': 'items-end justify-center',
                                'bottom-right': 'items-end justify-end'
                            };
                            return positions[this.position] || positions['center'];
                        },
                        getBackdropClasses() {
                            if (!this.backdrop) return '';
                            if (this.blur) return 'backdrop-blur-sm bg-white/30 dark:bg-black/30';
                            return 'bg-gray-500 bg-opacity-75 dark:bg-gray-900 dark:bg-opacity-75';
                        },
                        getIconClasses() {
                            const classes = {
                                'dialog-success': 'bg-green-100 text-green-600',
                                'dialog-info': 'bg-blue-100 text-blue-600',
                                'dialog-warning': 'bg-yellow-100 text-yellow-600',
                                'dialog-error': 'bg-red-100 text-red-600',
                            };
                            return classes[this.css] || 'bg-primary text-primary-content';
                        },
                        getConfirmButtonClasses() {
                            const classes = {
                                'dialog-success': 'btn-success',
                                'dialog-info': 'btn-info',
                                'dialog-warning': 'btn-warning',
                                'dialog-error': 'btn-error',
                            };
                            return classes[this.css] || 'btn-primary';
                        },
                        init() {
                            this.$watch('show', value => { document.body.style.overflow = value ? 'hidden' : ''; });
                        },
                        accept() {
                            if (this.confirmOptions?.method) Livewire.dispatch(this.confirmOptions.method, this.confirmOptions.params || []);
                            this.show = false;
                        },
                        reject() {
                            if (this.cancelOptions?.method) Livewire.dispatch(this.cancelOptions.method, this.cancelOptions.params || []);
                            this.show = false;
                        }
                    }"
                    @mary-dialog.window="
                        let dialog = $event.detail.dialog;
                        console.log(dialog);
                        title = dialog.title || '';
                        description = dialog.description || '';
                        icon = dialog.icon || null;
                        css = dialog.css || 'dialog-info';
                        position = dialog.position || position;
                        backdrop = dialog.backdrop || backdrop;
                        blur = dialog.blur || blur;
                        confirmOptions = dialog.confirmOptions || null;
                        cancelOptions = dialog.cancelOptions || null;
                        show = true;
                        Livewire.dispatch('setFocus', [{id: 'dialog-btn-confirm'}])
                    "
                >
                    <div
                        x-show="show"
                        x-transition:enter="ease-out duration-300"
                        x-transition:enter-start="opacity-0"
                        x-transition:enter-end="opacity-100"
                        x-transition:leave="ease-in duration-200"
                        x-transition:leave-start="opacity-100"
                        x-transition:leave-end="opacity-0"
                        class="fixed inset-0 z-50 flex transition-opacity p-4"
                        :class="getPositionClasses() + ' ' + getBackdropClasses()"
                        aria-labelledby="modal-title"
                        role="dialog"
                        aria-modal="true"
                        id="{{ $uuid }}"
                        @keydown.escape.window="reject()"
                    >
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden w-full max-w-md"
                            x-transition:enter="ease-out duration-300"
                            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                            x-transition:leave="ease-in duration-200"
                            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                            x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                            @click.away="reject()"
                            >
                            <div class="px-4 pt-3">
                                <div class="flex justify-end">
                                    <button class="btn btn-sm btn-circle border-0 shadow-none font-bold text-xl z-[999] bg-base-100" @click="reject()" type="button" id="dialog-btn-close">✕</button>
                                </div>
                                <div class="flex flex-col items-center">
                                    <div x-show="icon" x-html="icon" class="rounded-full p-2 mb-3" :class="getIconClasses()"></div>
                                    <div class="flex flex-col items-center gap-y-1">
                                        <h3 x-show="title" x-text="title" class="text-lg font-semibold text-gray-800 dark:text-gray-100" id="modal-title"></h3>
                                        <p x-show="description" x-text="description" class="text-sm text-gray-600 dark:text-gray-300"></p>
                                    </div>
                                </div>
                                {{ $slot ?? '' }}
                            </div>
                            <div class="flex *:flex-1 p-4">
                                <button x-show="cancelOptions && cancelOptions.text" @click="reject()" class="me-2 px-4 py-2 btn normal-case" id="dialog-btn-cancel">
                                    <span x-text="cancelOptions?.text"></span>
                                </button>
                                <button x-show="confirmOptions && confirmOptions.text" @click="accept()" class="px-4 py-2 btn" :class="getConfirmButtonClasses()" id="dialog-btn-confirm">
                                    <span x-text="confirmOptions?.text"></span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <script>
                    window.dialog = function(payload) {
                        setTimeout(() => window.dispatchEvent(new CustomEvent('mary-dialog', {detail: payload})), 50);
                    };

                    document.addEventListener('livewire:init', () => {
                        Livewire.hook('request', ({fail}) => {
                            fail(({status, content, preventDefault}) => {
                                try {
                                    let result = JSON.parse(content);
                                    if (result?.dialog && typeof window.dialog === "function") window.dialog(result)
                                    if ((result?.prevent_default ?? false) === true) preventDefault();
                                } catch (e) {
                                    console.error('Error processing dialog response:', e);
                                }
                            })
                        })
                    });
                </script>
            </div>
        HTML;
    }
}
