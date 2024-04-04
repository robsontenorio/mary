<?php

namespace Mary\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class ColorPicker extends Component
{
    public string $uuid;

    public function __construct(
        public ?string $label = null,
        public ?string $locale = 'en-US',
        public ?string $iconRight = 'o-swatch',
        public ?string $prefix = null,
        public ?string $suffix = null,
        public ?bool $omitError = false,
        public ?string $hint = null,

        // Slots
        public mixed $prepend = null,
        public mixed $append = null
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
            <div x-data="{
                    color: @entangle($attributes->wire('model')),
                    focused: false,
                    picker: false,
                    init() {
                        this.picker = Pickr.create({
                            el: $refs.colorPicker,
                            theme: 'nano',
                            useAsButton: true,
                            defaultRepresentation: 'HEX',
                            default: this.color,
                            swatches: [
                                'rgba(244, 67, 54, 1)',
                                'rgba(233, 30, 99, 1)',
                                'rgba(156, 39, 176, 1)',
                                'rgba(103, 58, 183, 1)',
                                'rgba(63, 81, 181, 1)',
                                'rgba(33, 150, 243, 1)',
                                'rgba(3, 169, 244, 1)'
                            ],
                            components: {
                                palette: true,
                                preview: true,
                                hex: false,
                                hue: true,
                                interaction: {
                                    hex: false,
                                    rgba: false,
                                    hsva: false,
                                    input: true,
                                }
                            }
                        })
                        this.picker.on('change', (v) => {
                            console.log(v)
                            this.color = v.toHEXA().toString();
                        });
                    },
                }"
                >
                @php
                    // Wee need this extra step to support models arrays. Ex: wire:model="emails.0"  , wire:model="emails.1"
                    $uuid = $uuid . $modelName()
                @endphp

                <!-- STANDARD LABEL -->
                @if($label)
                    <label for="{{ $uuid }}" class="pt-0 label label-text font-semibold">
                        <span>
                            {{ $label }}

                            @if($attributes->get('required'))
                                <span class="text-error">*</span>
                            @endif
                        </span>
                    </label>
                @endif

                <!-- PREFIX / PREPEND -->
                @if($prefix || $prepend)
                    <div
                        @class([
                                "rounded-l-lg flex items-center bg-base-200",
                                "border border-primary border-r-0 px-4" => $prefix,
                                "border-0 bg-base-300" => $attributes->has('disabled') && $attributes->get('disabled') == true,
                                "border-dashed" => $attributes->has('readonly') && $attributes->get('readonly') == true,
                                "!border-error" => $modelName() && $errors->has($modelName()) && !$omitError
                            ])
                    >
                        {{ $prepend ?? $prefix }}
                    </div>
                @endif

                <div
                    class="flex-1 relative"
                    id="{{ $uuid }}-container"
                >
                    <!-- INPUT -->
                    <input
                        id="{{ $uuid }}"
                        x-ref="colorPicker"
                        placeholder = "{{ $attributes->whereStartsWith('placeholder')->first() }} "
                        x-model="color"
                        readonly
                        {{
                            $attributes
                                ->merge(['type' => 'text'])
                                ->class([
                                    'input input-primary w-full peer',
                                    'pl-10' => (true),
                                    'rounded-l-none' => $prefix || $prepend,
                                    'rounded-r-none' => $suffix || $append,
                                    'border border-dashed' => $attributes->has('readonly') && $attributes->get('readonly') == true,
                                    'input-error' => $modelName() && $errors->has($modelName()) && !$omitError
                            ])
                        }}
                    />

                    <x-mary-icon name="s-square-2-stack" class="absolute top-1/2 -translate-y-1/2 left-3 pointer-events-none" x-bind:style="`color: ${color}`  " />

                    <!-- RIGHT ICON  -->
                    @if($iconRight)
                        <x-mary-icon :name="$iconRight" @class(["absolute top-1/2 right-3 -translate-y-1/2 text-gray-400 pointer-events-none", "!right-10" => false]) />
                    @endif
                </div>

                <!-- SUFFIX/APPEND -->
                @if($suffix || $append)
                     <div
                        @class([
                                "rounded-r-lg flex items-center bg-base-200",
                                "border border-primary border-l-0 px-4" => $suffix,
                                "border-0 bg-base-300" => $attributes->has('disabled') && $attributes->get('disabled') == true,
                                "border-dashed" => $attributes->has('readonly') && $attributes->get('readonly') == true,
                                "!border-error" => $modelName() && $errors->has($modelName()) && !$omitError
                            ])
                    >
                        {{ $append ?? $suffix }}
                    </div>
                @endif

                <!-- END: PREFIX/SUFFIX/APPEND/PREPEND CONTAINER  -->
                @if($prefix || $suffix || $prepend || $append)
                    </div>
                @endif

                <!-- ERROR -->
                @if(!$omitError && $modelName())
                    @error($modelName())
                        <div class="text-red-500 label-text-alt p-1">{{ $message }}</div>
                    @enderror
                @endif

                <!-- HINT -->
                @if($hint)
                    <div class="label-text-alt text-gray-400 p-1 pb-0">{{ $hint }}</div>
                @endif
            </div>
            HTML;
    }
}
