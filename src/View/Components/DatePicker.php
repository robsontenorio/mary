<?php

namespace Mary\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Arr;
use Illuminate\View\Component;

class DatePicker extends Component
{
    public string $uuid;

    public function __construct(
        public ?string $label = null,
        public ?string $icon = null,
        public ?string $iconRight = null,
        public ?string $hint = null,
        public ?string $hintClass = 'label-text-alt text-gray-400 py-1 pb-0',
        public ?bool $inline = false,
        public ?array $config = [],
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

    public function setup(): string
    {
        // Handle `wire:model.live` for `range` dates
        if (isset($this->config["mode"]) && $this->config["mode"] == "range" && $this->attributes->wire('model')->hasModifier('live')) {
            $this->attributes->setAttributes([
                'wire:model' => $this->modelName(),
                'live' => true
            ]);
        }

        $config = json_encode(array_merge([
            'dateFormat' => 'Y-m-d H:i',
            'altInput' => true,
            'clickOpens' => ! $this->attributes->has('readonly') || $this->attributes->get('readonly') == false,
            'defaultDate' => '#model#',
            'plugins' => ['#plugins#'],
        ], Arr::except($this->config, ["plugins"])));

        // Plugins
        $plugins = "";

        foreach (Arr::get($this->config, 'plugins', []) as $plugin) {
            $plugins .= "new " . key($plugin) . "( " . json_encode(current($plugin)) . " ),";
        }

        // Plugins
        $config = str_replace('"#plugins#"', $plugins, $config);

        // Sets default date as current bound model
        $config = str_replace('"#model#"', '$wire.get("' . $this->modelName() . '")', $config);

        return $config;
    }

    public function render(): View|Closure|string
    {
        return <<<'HTML'
            <div wire:key="datepicker-{{ rand() }}">
                <!-- STANDARD LABEL -->
                @if($label && !$inline)
                    <div class="pt-0 label label-text font-semibold">
                        <span>
                            {{ $label }}

                            @if($attributes->get('required'))
                                <span class="text-error">*</span>
                            @endif
                        </span>
                    </div>
                @endif

                <div class="flex-1 relative">
                        <div
                            x-data="{instance: undefined}"
                            x-init="instance = flatpickr($refs.input, {{ $setup() }});"
                            @if(isset($config["mode"]) && $config["mode"] == "range" && $attributes->get('live'))
                                @change="const value = $event.target.value; if(value.split('to').length == 2) { $wire.set('{{ $modelName() }}', value) };"
                            @endif
                            x-on:livewire:navigating.window="instance.destroy();"
                        >
                            <input
                                x-ref="input"
                                {{
                                    $attributes
                                        ->merge(['type' => 'date'])
                                        ->class([
                                            "input input-primary w-full peer appearance-none",
                                            'ps-10' => ($icon),
                                            'h-14' => ($inline),
                                            'pt-3' => ($inline && $label),
                                            'border border-dashed' => $attributes->has('readonly') && $attributes->get('readonly') == true,
                                            'input-error' => $errors->has($errorFieldName())
                                        ])
                                }}
                            />
                        </div>

                    <!-- ICON  -->
                    @if($icon)
                        <x-mary-icon :name="$icon" class="absolute top-1/2 -translate-y-1/2 start-3 text-gray-400 pointer-events-none" />
                    @endif

                    <!-- RIGHT ICON  -->
                    @if($iconRight)
                        <x-mary-icon :name="$iconRight" class="absolute top-1/2 end-3 -translate-y-1/2 text-gray-400 pointer-events-none" />
                    @endif

                    <!-- INLINE LABEL -->
                    @if($label && $inline)
                        <div class="absolute text-gray-400 duration-300 transform -translate-y-1 scale-75 top-2 origin-[0] rounded px-2 peer-focus:px-2 peer-focus:text-blue-600 peer-focus:dark:text-blue-500 peer-placeholder-shown:scale-100 peer-placeholder-shown:-translate-y-1/2 peer-placeholder-shown:top-1/2 peer-focus:top-2 peer-focus:scale-75 peer-focus:-translate-y-1 @if($inline && $icon) start-9 @else start-3 @endif">
                            {{ $label }}
                        </div>
                    @endif

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
                    <div class="{{ $hintClass }}" x-classes="label-text-alt text-gray-400 py-1 pb-0">{{ $hint }}</div>
                @endif

            </div>
            HTML;
    }
}
