<?php

namespace Mary\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Input extends Component
{
    public string $uuid;

    public function __construct(
        public ?string $id = null,
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
        public ?string $popover = null,
        public ?string $popoverIcon = "o-question-mark-circle",

        // Slots
        public mixed $prepend = null,
        public mixed $append = null,

        // Validations
        public ?string $errorField = null,
        public ?string $errorClass = 'text-error',
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

    public function isReadonly(): bool
    {
        return $this->attributes->has('readonly') && $this->attributes->get('readonly') == true;
    }

    public function isDisabled(): bool
    {
        return $this->attributes->has('disabled') && $this->attributes->get('disabled') == true;
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

                            {{-- INPUT POPOVER --}}
                            @if($popover)
                                <x-mary-popover offset="5" position="top-start">
                                    <x-slot:trigger>
                                        <x-mary-icon :name="$popoverIcon" class="w-4 h-4 opacity-40 mb-0.5" />
                                    </x-slot:trigger>
                                    <x-slot:content>
                                        {{ $popover }}
                                    </x-slot:content>
                                </x-mary-popover>
                            @endif
                        </legend>
                    @endif

                    <label @class(["floating-label" => $label && $inline])>
                        {{-- FLOATING LABEL--}}
                        @if ($label && $inline)
                            <span class="font-semibold">{{ $label }}</span>
                        @endif

                        <div @class(["w-full", "join" => $prepend || $append])>
                            {{-- PREPEND --}}
                            @if($prepend)
                                {{ $prepend }}
                            @endif

                            {{-- THE LABEL THAT HOLDS THE INPUT --}}
                            <label
                                @if($isDisabled())
                                    disabled
                                @endif

                                {{
                                    $attributes->whereStartsWith('class')->class([
                                        "input w-full",
                                        "join-item" => $prepend || $append,
                                        "border-dashed" => $isReadonly(),
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
                                            x-on:input="$nextTick(() => $wire.set('{{ $modelName() }}', Currency.getUnmasked(), {{ json_encode($attributes->wire('model')->hasModifier('live')) }}))"
                                            x-on:blur="$nextTick(() => $wire.set('{{ $modelName() }}', Currency.getUnmasked(), {{ json_encode($attributes->wire('model')->hasModifier('blur')) }}))"
                                            inputmode="numeric"
                                        @endif

                                        {{
                                            $attributes
                                                ->merge(['type' => 'text'])
                                                ->except($money ? ['wire:model', 'wire:model.live', 'wire:model.blur'] : '')
                                        }}
                                    />

                                {{-- HIDDEN MONEY INPUT + END MONEY SETUP --}}
                                @if($money)
                                        <input type="hidden" {{ $attributes->wire('model') }} />
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
