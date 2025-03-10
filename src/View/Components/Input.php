<?php

namespace Mary\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Input extends Component
{
    public string $uuid;

    public function __construct(
        public ?string $label = null,
        public ?string $icon = null,
        public ?string $iconRight = null,
        public ?string $hint = null,
        public ?string $hintClass = 'fieldset-label',
        public ?string $prefix = null,
        public ?string $suffix = null,
        public ?bool $inline = false,
        public ?bool $clearable = false,
        public ?bool $money = false,
        public ?string $locale = 'en-US',

        // Slots
        public mixed $prepend = null,
        public mixed $append = null,

        // Validations
        public ?string $errorField = null,
        public ?string $errorClass = 'text-error',
        public ?bool $omitError = false,
        public ?bool $firstErrorOnly = false,
    ) {
        $this->uuid = "mary" . md5(serialize($this));
    }

    public function modelName(): ?string
    {
        return $this->attributes->whereStartsWith('wire:model')->first();
    }

    public function errorFieldName(): ?string
    {
        return $this->errorField ?? $this->modelName();
    }

    public function moneySettings(): string
    {
        return json_encode([
            'init' => true,
            'maskOpts' => [
                'locales' => $this->locale
            ]
        ]);
    }

    public function render(): View|Closure|string
    {
        return <<<'BLADE'
            <div>
                @php
                    // We need this extra step to support models arrays. Ex: wire:model="emails.0"  , wire:model="emails.1"
                    $uuid = $uuid . $modelName()
                @endphp

                <fieldset class="fieldset py-0">
                    {{-- STANDARD LABEL --}}
                    @if($label && !$inline)
                        <legend class="fieldset-legend mb-0.5">
                            {{ $label }}

                            @if($attributes->get('required'))
                                <span class="text-error">*</span>
                            @endif
                        </legend>
                    @endif

                    <label @class(["floating-label" => $label && $inline])>
                        {{-- FLOATING LABEL--}}
                        @if ($label && $inline)
                            <span class="text-[1.3em] font-semibold">{{ $label }}</span>
                        @endif

                        <div @class(["w-full", "join" => $prepend || $append])>
                            {{-- PREPEND --}}
                            @if($prepend)
                                {{ $prepend }}
                            @endif

                            {{-- THE LABEL THAT HOLDS THE INPUT --}}
                            <label
                                {{
                                    $attributes->whereStartsWith('class')->class([
                                        "input w-full",
                                        "join-item" => $prepend || $append,
                                        "border-dashed" => $attributes->has("readonly") && $attributes->get("readonly") == true,
                                        "!input-error" => $errorFieldName() && $errors->has($errorFieldName()) && !$omitError
                                    ])
                                }}
                             >
                                {{-- PREFIX --}}
                                @if($prefix)
                                    <span class="label">{{ $prefix }}</span>
                                @endif

                                {{-- ICON LEFT --}}
                                @if($icon)
                                    <x-mary-icon :name="$icon" class="pointer-events-none w-4 h-4 opacity-40" />
                                @endif

                                {{-- MONEY SETUP --}}
                                @if($money)
                                    <div
                                        class="w-full"
                                        wire:key="money-{{ rand() }}"
                                        x-data="{ amount: $wire.get('{{ $modelName() }}') }" x-init="$nextTick(() => new Currency($refs.myInput, {{ $moneySettings() }}))"
                                    >
                                @endif

                                    {{-- INPUT --}}
                                    <input
                                        id="{{ $uuid }}"
                                        placeholder="{{ $attributes->get('placeholder') }} "

                                        @if($attributes->has('autofocus') && $attributes->get('autofocus') == true)
                                            autofocus
                                        @endif

                                        @if($money)
                                            x-ref="myInput"
                                            :value="amount"
                                            x-on:input="$nextTick(() => $wire.set('{{ $modelName() }}', Currency.getUnmasked(), false))"
                                            inputmode="numeric"
                                        @endif

                                        {{
                                            $attributes
                                                ->merge(['type' => 'text'])
                                                ->except($money ? 'wire:model' : '')
                                        }}
                                    />

                                {{-- HIDDEN MONEY INPUT + END MONEY SETUP --}}
                                @if($money)
                                        <input type="hidden" {{ $attributes->only('wire:model') }} />
                                    </div>
                                @endif

                                {{-- CLEAR ICON  --}}
                                @if($clearable)
                                    <x-mary-icon x-on:click="$wire.set('{{ $modelName() }}', '', {{ json_encode($attributes->wire('model')->hasModifier('live')) }})"  name="o-x-mark" class="cursor-pointer w-4 h-4 opacity-40"/>
                                @endif

                                {{-- ICON RIGHT --}}
                                @if($iconRight)
                                    <x-mary-icon :name="$iconRight" class="pointer-events-none w-4 h-4 opacity-40" />
                                @endif

                                {{-- SUFFIX --}}
                                @if($suffix)
                                    <span class="label">{{ $suffix }}</span>
                                @endif
                            </label>

                            {{-- APPEND --}}
                            @if($append)
                                {{ $append }}
                            @endif
                        </div>
                    </label>

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

                    {{-- HINT --}}
                    @if($hint)
                        <div class="{{ $hintClass }}" x-classes="fieldset-label">{{ $hint }}</div>
                    @endif
                </fieldset>
            </div>
            BLADE;
    }
}
