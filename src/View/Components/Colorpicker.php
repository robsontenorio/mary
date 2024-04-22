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
        public ?bool $inline = false,
        public ?bool $clearable = false,

        // Validations
        public ?string $errorField = null,
        public ?string $errorClass = 'text-red-500 label-text-alt p-1',
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
                                "rounded-l-lg flex items-center",
                                "border border-primary border-r-0 px-4 cursor-pointer",
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
                                        'input input-primary w-full peer',
                                        'pl-10' => ($icon),
                                        'h-14' => ($inline),
                                        'pt-3' => ($inline && $label),
                                        'rounded-l-none',
                                        'border border-dashed' => $attributes->has('readonly') && $attributes->get('readonly') == true,
                                        'input-error' => $errorFieldName() && $errors->has($errorFieldName()) && !$omitError
                                ])
                            }}
                        />

                        <!-- ICON  -->
                        @if($icon)
                            <x-mary-icon :name="$icon" class="absolute top-1/2 -translate-y-1/2 left-3 text-gray-400 cursor-pointer" x-on:click="$refs.colorpicker.click()" />
                        @endif

                        <!-- CLEAR ICON  -->
                        @if($clearable)
                            <x-mary-icon @click="$wire.set('{{ $modelName() }}', '', {{ json_encode($attributes->wire('model')->hasModifier('live')) }})"  name="o-x-mark" class="absolute top-1/2 right-3 -translate-y-1/2 cursor-pointer text-gray-400 hover:text-gray-600" />
                        @endif

                        <!-- RIGHT ICON  -->
                        @if($iconRight)
                            <x-mary-icon :name="$iconRight" @class(["absolute top-1/2 right-3 -translate-y-1/2 text-gray-400 cursor-pointer", "!right-10" => $clearable]) x-on:click="$refs.colorpicker.click()" />
                        @endif

                        <!-- INLINE LABEL -->
                        @if($label && $inline)
                            <label for="{{ $uuid }}" class="absolute text-gray-400 duration-300 transform -translate-y-1 scale-75 top-2 origin-[0] rounded px-2 peer-focus:px-2 peer-focus:text-blue-600 peer-focus:dark:text-blue-500 peer-placeholder-shown:scale-100 peer-placeholder-shown:-translate-y-1/2 peer-placeholder-shown:top-1/2 peer-focus:top-2 peer-focus:scale-75 peer-focus:-translate-y-1 @if($inline && $icon) left-9 @else left-3 @endif">
                                {{ $label }}
                            </label>
                        @endif
                    </div>
                </div>

                <!-- ERROR -->
                @if(!$omitError && $errors->has($errorFieldName()))
                    @foreach($errors->get($errorFieldName()) as $message)
                        @foreach(Arr::wrap($message) as $line)
                            <div class="{{ $errorClass }}" x-classes="text-red-500 label-text-alt p-1">{{ $line }}</div>
                            @break($firstErrorOnly)
                        @endforeach
                        @break($firstErrorOnly)
                    @endforeach
                @endif

                <!-- HINT -->
                @if($hint)
                    <div class="label-text-alt text-gray-400 p-1 pb-0">{{ $hint }}</div>
                @endif
            </div>
            HTML;
    }
}
