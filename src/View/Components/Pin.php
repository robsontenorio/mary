<?php

namespace Mary\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Pin extends Component
{
    public string $uuid;

    public function __construct(
        public int $size,
        public ?bool $numeric = false,

    ) {
        $this->uuid = "mary" . md5(serialize($this));
    }

    public function modelName(): ?string
    {
        return $this->attributes->whereStartsWith('wire:model')->first();
    }

    public function render(): View|Closure|string
    {
        return <<<'HTML'
                <div {{ $attributes->except('wire:model') }}>
                    <div
                        x-data="{
                                value: @entangle($attributes->wire('model')),
                                inputs: [],
                                init() {
                                    // Copy & Paste
                                    document.getElementById('pin{{ $uuid }}').addEventListener('paste', (e) => {
                                        const paste = (e.clipboardData || window.clipboardData).getData('text');

                                         for (var i = 0; i < {{ $size }}; i++) {
                                            this.inputs[i] = paste[i];
                                        }

                                        e.preventDefault()
                                        this.handlePin()
                                    })
                                },
                                next(el) {
                                    this.handlePin()

                                    if (el.value.length == 0) {
                                        return
                                    }

                                    if (el.nextElementSibling) {
                                        el.nextElementSibling.focus()
                                        el.nextElementSibling.select()
                                    }
                                },
                                remove(el, i) {
                                    this.inputs[i] = ''
                                    this.handlePin()

                                    if (el.previousElementSibling) {
                                        el.previousElementSibling.focus()
                                        el.previousElementSibling.select()
                                    }
                                },
                                handlePin() {
                                    this.value = this.inputs.join('')

                                    this.value.length === {{ $size }}
                                        ? this.$dispatch('completed', this.value)
                                        : this.$dispatch('incomplete', this.value)
                                }
                        }"
                    >
                        <div class="flex gap-3" id="pin{{ $uuid }}">
                            @foreach(range(0, $size - 1) as $i)
                                <input
                                    type="text"
                                    class="input input-primary !w-14 font-black text-2xl text-center"
                                    maxlength="1"
                                    x-model="inputs[{{ $i }}]"
                                    @keydown.space.prevent
                                    @keydown.backspace.prevent="remove($event.target, {{ $i }})"
                                    @input="next($event.target)"
                                    @if($numeric)
                                        inputmode="numeric"
                                        x-mask="9"
                                    @endif />
                            @endforeach
                        </div>
                    </div>
                </div>
            HTML;
    }
}
