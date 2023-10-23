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
        public ?string $prefix = null,
        public ?string $suffix = null,
        public ?bool $inline = false,
        public ?bool $money = false,
        public ?string $locale = 'en-US',

        // Slots
        public mixed $prepend = null,
        public mixed $append = null
    ) {
        $this->uuid = md5(serialize($this));
    }

    public function modelName(): ?string
    {
        return $this->attributes->whereStartsWith('wire:model')->first();
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
        return <<<'HTML'
            <div>
                <!-- STANDARD LABEL -->
                @if($label && !$inline)
                    <label for="{{ $uuid }}" class="pt-0 label label-text font-semibold">{{ $label }}</label>
                @endif

                <!-- PREFIX/SUFFIX/PREPEND/APPEND CONTAINER -->
                @if($prefix || $suffix || $prepend || $append)
                    <div class="flex">
                @endif

                <!-- PREFIX / PREPEND -->
                @if($prefix || $prepend)
                    <div class="rounded-l-lg flex items-center bg-base-200 @if($prefix) border border-primary border-r-0 px-4 @endif">
                        {{ $prepend ?? $prefix }}
                    </div>
                @endif

                <div class="flex-1 relative">
                    <!-- MONEY SETUP -->
                    @if($money)
                        <div
                            wire:key="money-{{ rand() }}"
                            x-data="{ amount: $wire.{{ $modelName() }} }" x-init="$nextTick(() => new Currency($refs.myInput, {{ $moneySettings() }}))"
                        >
                    @endif

                    <!-- INPUT -->
                    <input
                        id="{{ $uuid }}"
                        placeholder = "{{ $attributes->whereStartsWith('placeholder')->first() }} "
                        x-ref="myInput"

                        @if($money)
                            :value="amount"
                            @input="$nextTick(() => $wire.{{ $modelName() }} = Currency.getUnmasked())"
                            inputmode="numeric"
                        @endif

                        {{
                            $attributes
                                ->merge(['type' => 'text'])
                                ->except($money ? 'wire:model' : '')
                                ->class([
                                    'input input-primary w-full peer',
                                    'pl-10' => ($icon),
                                    'h-14' => ($inline),
                                    'pt-3' => ($inline && $label),
                                    'rounded-l-none' => $prefix || $prepend,
                                    'rounded-r-none' => $suffix || $append,
                                    'border border-dashed' => $attributes->has('readonly') && $attributes->get('readonly') == true,
                                    'input-error' => $errors->has($modelName())
                            ])
                        }}
                    />

                    <!-- ICON  -->
                    @if($icon)
                        <x-icon :name="$icon" class="absolute top-1/2 -translate-y-1/2 left-3 text-gray-400 pointer-events-none" />
                    @endif

                    <!-- RIGHT ICON  -->
                    @if($iconRight)
                        <x-icon :name="$iconRight" class="absolute top-1/2 right-3 -translate-y-1/2 text-gray-400 pointer-events-none" />
                    @endif

                    <!-- INLINE LABEL -->
                    @if($label && $inline)
                        <label for="{{ $uuid }}" class="absolute text-gray-400 duration-300 transform -translate-y-1 scale-75 top-2 origin-[0] rounded bg-base-100 px-2 peer-focus:px-2 peer-focus:text-blue-600 peer-focus:dark:text-blue-500 peer-placeholder-shown:scale-100 peer-placeholder-shown:-translate-y-1/2 peer-placeholder-shown:top-1/2 peer-focus:top-2 peer-focus:scale-75 peer-focus:-translate-y-1 @if($inline && $icon) left-9 @else left-3 @endif">
                            {{ $label }}
                        </label>
                    @endif

                    <!-- HIDDEN MONEY INPUT + END MONEY SETUP -->
                    @if($money)
                            <input type="hidden" {{ $attributes->only('wire:model') }} />
                        </div>
                    @endif
                </div>

                <!-- SUFFIX/APPEND -->
                @if($suffix || $append)
                    <div class="rounded-r-lg flex items-center bg-base-200 @if($suffix) border border-primary border-l-0 px-4 @endif">
                        {{ $append ?? $suffix }}
                    </div>
                @endif

                <!-- END: PREFIX/SUFFIX/APPEND/PREPEND CONTAINER  -->
                @if($prefix || $suffix || $prepend || $append)
                    </div>
                @endif

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
