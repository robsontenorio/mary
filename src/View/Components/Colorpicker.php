<?php

namespace Mary\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Colorpicker extends Component
{
    public string $uuid;

    public function __construct(
        public ?string $id = null,
        public ?string $label = null,
        public ?string $icon = '',
        public ?string $iconRight = null,
        public ?string $hint = null,
        public ?string $hintClass = 'fieldset-label',
        public ?string $prefix = null,
        public ?string $suffix = null,
        public ?bool $inline = false,
        public ?bool $clearable = false,

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
                            <span class="font-semibold ml-10">{{ $label }}</span>
                        @endif

                        <div class="w-full join">
                             {{-- COLOR PICKER --}}
                             <label
                                x-on:click="$refs.colorpicker.click()"
                                :class="!$wire.{{ $modelName() }} && 'bg-[repeating-linear-gradient(45deg,_#ddd_0px,_#ddd_1px,_transparent_1px,_transparent_5px)]'"
                                :style="{ backgroundColor: $wire.{{ $modelName() }} }"
                                @class(["input join-item w-12 p-0", "border border-dashed" => $isReadonly()])
                             >
                                <input
                                    type="color"
                                    class="cursor-pointer opacity-0 join-item"
                                    x-ref="colorpicker"
                                    x-on:click.stop=""
                                    :style="{ backgroundColor: $wire.{{ $modelName() }} }"
                                    @class(["border-dashed" => $isReadonly()])
                                    {{ $attributes->wire('model') }}

                                    @if($isDisabled() || $isReadonly())
                                        disabled
                                    @endif
                                />
                            </label>

                            {{-- THE LABEL THAT HOLDS THE INPUT --}}
                            <label
                                {{
                                    $attributes->whereStartsWith('class')->class([
                                        "input join-item w-full",
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
                                    <x-mary-icon :name="$icon" class="pointer-events-none w-4 h-4 -ml-1 opacity-40" />
                                @endif

                                {{-- INPUT --}}
                                <input
                                    id="{{ $uuid }}"
                                    placeholder="{{ $attributes->get('placeholder') }} "
                                    {{ $attributes->merge(['type' => 'text']) }}
                                />

                                {{-- ICON RIGHT --}}
                                @if($iconRight)
                                    <x-mary-icon :name="$iconRight" class="pointer-events-none w-4 h-4 opacity-40" />
                                @endif

                                {{-- SUFFIX --}}
                                @if($suffix)
                                    <span class="label">{{ $suffix }}</span>
                                @endif
                            </label>
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
