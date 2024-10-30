<?php

namespace Mary\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Colorpicker extends Component
{
    public string $uuid;

    public function __construct(
        public ?string $label = null,
        public ?string $icon = '',
        public ?string $iconRight = null,
        public ?string $hint = null,
        public ?string $hintClass = 'label-text-alt text-base-content/50 py-1 pb-0',
        public ?bool $inline = false,
        public ?bool $clearable = false,

        // Validations
        public ?string $errorField = null,
        public ?string $errorClass = 'text-error label-text-alt p-1',
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

    public function getInputClasses(): ?string
    {
        return str($this->attributes->get('class'))->matchAll('/input-\w+/')->prepend("input")->join(" ");
    }

    public function render(): View|Closure|string
    {
        return <<<'HTML'
            <div>
                @php
                    // Wee need this extra step to support models arrays. Ex: wire:model="emails.0"  , wire:model="emails.1"
                    $uuid = $uuid . $modelName()
                @endphp

                <!-- STANDARD LABEL -->
                @if($label && !$inline)
                    <label for="{{ $uuid }}" class="pt-0 label label-text font-semibold">
                        <span>
                            {{ $label }}

                            @if($attributes->get('required'))
                                <span class="text-error">*</span>
                            @endif
                        </span>
                    </label>
                @endif

                <div class="flex" x-data>
                    <div
                        @class([
                                "input input-bordered h-auto rounded-s-lg flex items-center !bg-base-200",
                                "$getInputClasses rounded-e-none border-e-0 px-4",
                                "focus-within:outline focus-within:outline-2 focus-within:outline-offset-2",
                                "border-0 bg-base-300" => $attributes->has('disabled') && $attributes->get('disabled') == true,
                                "border-dashed" => $attributes->has('readonly') && $attributes->get('readonly') == true,
                                "!border-error" => $errorFieldName() && $errors->has($errorFieldName()) && !$omitError
                            ])

                            x-on:click="$refs.colorpicker.click()"
                            :style="{ backgroundColor: $wire.{{ $modelName() }} }"
                    >
                        <input
                            type="color"
                            class="cursor-pointer opacity-0 w-4"
                            x-ref="colorpicker"
                            x-on:click.stop=""

                            @if($attributes->has('disabled') && $attributes->get('disabled') == true)
                                disabled
                            @endif

                            @if($attributes->has('readonly') && $attributes->get('readonly') == true)
                                disabled
                            @endif

                            {{ $attributes->wire('model') }}
                            :style="{ backgroundColor: $wire.{{ $modelName() }} }"  />
                    </div>

                    <div class="flex-1 relative">
                        <!-- INPUT -->
                        <input
                            id="{{ $uuid }}"
                            placeholder = "{{ $attributes->whereStartsWith('placeholder')->first() }} "
                            {{
                                $attributes
                                    ->merge(['type' => 'text'])
                                    ->class([
                                        'input input-bordered w-full peer',
                                        'ps-10' => ($icon),
                                        'h-14' => ($inline),
                                        'pt-3' => ($inline && $label),
                                        'rounded-s-none',
                                        'border border-dashed' => $attributes->has('readonly') && $attributes->get('readonly') == true,
                                        '!border-base-300' => $attributes->has('disabled') && $attributes->get('disabled') == true,
                                        'input-error' => $errorFieldName() && $errors->has($errorFieldName()) && !$omitError
                                ])
                            }}
                        />

                        <!-- ICON  -->
                        @if($icon)
                            <x-mary-icon :name="$icon" class="absolute top-1/2 -translate-y-1/2 start-3 text-base-content/50 cursor-pointer" x-on:click="$refs.colorpicker.click()" />
                        @endif

                        <!-- CLEAR ICON  -->
                        @if($clearable)
                            <x-mary-icon @click="$wire.set('{{ $modelName() }}', '', {{ json_encode($attributes->wire('model')->hasModifier('live')) }})"  name="o-x-mark" class="absolute top-1/2 end-3 -translate-y-1/2 cursor-pointer text-base-content/50 hover:text-base-content/80" />
                        @endif

                        <!-- RIGHT ICON  -->
                        @if($iconRight)
                            <x-mary-icon :name="$iconRight" @class(["absolute top-1/2 end-3 -translate-y-1/2 text-base-content/50 cursor-pointer", "!end-10" => $clearable]) x-on:click="$refs.colorpicker.click()" />
                        @endif

                        <!-- INLINE LABEL -->
                        @if($label && $inline)
                            <label for="{{ $uuid }}" class="absolute text-base-content/50 duration-300 transform -translate-y-1 scale-75 top-2 origin-[0] rounded px-2 peer-focus:px-2 peer-focus:text-blue-600 peer-focus:dark:text-blue-500 peer-placeholder-shown:scale-100 peer-placeholder-shown:-translate-y-1/2 peer-placeholder-shown:top-1/2 peer-focus:top-2 peer-focus:scale-75 peer-focus:-translate-y-1 @if($inline && $icon) start-9 @else start-3 @endif">
                                {{ $label }}
                            </label>
                        @endif
                    </div>
                </div>

                <!-- ERROR -->
                @if(!$omitError && $errors->has($errorFieldName()))
                    @foreach($errors->get($errorFieldName()) as $message)
                        @foreach(Arr::wrap($message) as $line)
                            <div class="{{ $errorClass }}" x-classes="text-error label-text-alt p-1">{{ $line }}</div>
                            @break($firstErrorOnly)
                        @endforeach
                        @break($firstErrorOnly)
                    @endforeach
                @endif

                <!-- HINT -->
                @if($hint)
                    <div class="{{ $hintClass }}" x-classes="label-text-alt text-base-content/50 py-1 pb-0">{{ $hint }}</div>
                @endif
            </div>
            HTML;
    }
}
