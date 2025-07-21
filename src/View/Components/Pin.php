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
        public ?string $id = null,
        public ?bool $numeric = false,
        public ?bool $hide = false,
        public ?string $hideType = "disc",
        public ?bool $noGap = false,

        // Validations
        public ?string $errorField = null,
        public ?string $errorClass = 'text-error text-xs pt-2',
        public ?bool $omitError = false,
        public ?bool $firstErrorOnly = false,

    ) {
        $this->uuid = "mary" . md5(serialize($this)) . $id;
    }

    public function modelName(): ?string
    {
        return $this->attributes->whereStartsWith('wire:model')->first();
    }

    public function errorFieldName(): ?string
    {
        return $this->errorField ?? $this->modelName();
    }

    public function render(): View|Closure|string
    {
        return <<<'HTML'
                <div>
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
                        <div
                            @class(["flex", "join" => $noGap, "gap-3" => !$noGap])
                            id="pin{{ $uuid }}"
                        >
                            @foreach(range(0, $size - 1) as $i)
                                <input
                                    @style([
                                        $hide ? "text-security: $hideType;
                                                -webkit-text-security: $hideType;
                                                -moz-text-security $hideType;
                                                " : "",
                                    ])
                                    id="{{ $uuid }}-pin-{{ $i }}"
                                    type="text"
                                    maxlength="1"
                                    x-model="inputs[{{ $i }}]"
                                    @keydown.space.prevent
                                    @keydown.backspace.prevent="remove($event.target, {{ $i }})"
                                    @input="next($event.target)"
                                    @if($numeric)
                                        inputmode="numeric"
                                        x-mask="9"
                                    @endif
                                    {{
                                        $attributes->whereDoesntStartWith('wire')->class([
                                            "input input-border min-w-6 max-w-12 p-0 font-bold text-xl text-center",
                                            "join-item" => $noGap,
                                            "!input-error" => $errorFieldName() && $errors->has($errorFieldName()) && !$omitError
                                        ])
                                    }}
                                />
                            @endforeach
                        </div>

                        {{-- ERROR --}}
                        @if(!$omitError && $errors->has($errorFieldName()))
                            @foreach($errors->get($errorFieldName()) as $message)
                                @foreach(Arr::wrap($message) as $line)
                                    <div class="{{ $errorClass }}" x-class="text-error">{{ $line }}</div>
                                    @break($firstErrorOnly)
                                @endforeach
                                @break($firstErrorOnly)
                            @endforeach
                        @endif
                    </div>
                </div>
            HTML;
    }
}
