<?php

namespace Mary\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class DatePicker extends Component
{
    public string $uuid;

    public function __construct(
        public ?string $label = null,
        public ?string $icon = null,
        public ?string $iconRight = null,
        public ?string $hint = null,
        public ?bool $inline = false,
        public ?array $config = []
    ) {
        $this->uuid = "mary" . md5(serialize($this));
    }

    public function setup(): string
    {
        $config = json_encode(array_merge([
            'dateFormat' => 'Y-m-d H:i',
            'altInput' => true,
            'clickOpens' => !$this->attributes->has('readonly') || $this->attributes->get('readonly') == false,
            'defaultDate' => 'x',
        ], $this->config));

        // Sets default date as current binded model
        $config = str_replace('"x"', '$wire.' . $this->modelName(), $config);

        return $config;
    }

    public function modelName(): ?string
    {
        return $this->attributes->whereStartsWith('wire:model')->first();
    }

    public function render(): View|Closure|string
    {
        return <<<'HTML'
            <div wire:key="datepicker-{{ rand() }}">
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

                <div class="flex-1 relative">
                        <div
                            x-data="{instance: undefined}"
                            x-init="instance = flatpickr($refs.input, {{ $setup() }});"
                            x-on:livewire:navigating.window="instance.destroy();"
                        >
                            <input
                                x-ref="input"
                                {{
                                    $attributes
                                        ->merge(['type' => 'date'])
                                        ->class([
                                            "input input-primary w-full peer appearance-none",
                                            'pl-10' => ($icon),
                                            'h-14' => ($inline),
                                            'pt-3' => ($inline && $label),
                                            'border border-dashed' => $attributes->has('readonly') && $attributes->get('readonly') == true,
                                            'input-error' => $errors->has($modelName())
                                        ])
                                }}
                            />
                        </div>

                    <!-- ICON  -->
                    @if($icon)
                        <x-mary-icon :name="$icon" class="absolute top-1/2 -translate-y-1/2 left-3 text-gray-400 pointer-events-none" />
                    @endif

                    <!-- RIGHT ICON  -->
                    @if($iconRight)
                        <x-mary-icon :name="$iconRight" class="absolute top-1/2 right-3 -translate-y-1/2 text-gray-400 pointer-events-none" />
                    @endif

                    <!-- INLINE LABEL -->
                    @if($label && $inline)
                        <label for="{{ $uuid }}" class="absolute text-gray-400 duration-300 transform -translate-y-1 scale-75 top-2 origin-[0] bg-white rounded dark:bg-gray-900 px-2 peer-focus:px-2 peer-focus:text-blue-600 peer-focus:dark:text-blue-500 peer-placeholder-shown:scale-100 peer-placeholder-shown:-translate-y-1/2 peer-placeholder-shown:top-1/2 peer-focus:top-2 peer-focus:scale-75 peer-focus:-translate-y-1 @if($inline && $icon) left-9 @else left-3 @endif">
                            {{ $label }}
                        </label>
                    @endif

                </div>

                <!-- ERROR -->
                @error($modelName())
                    <div class="text-red-500 label-text-alt p-1">{{ $message }}</div>
                @enderror

                <!-- HINT -->
                @if($hint)
                    <div class="label-text-alt text-gray-400 p-1 pb-0">{{ $hint }}</div>
                @endif

            </div>
            HTML;
    }
}
