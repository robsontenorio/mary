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
        public ?string $id = null,
        public ?string $label = null,
        public ?string $icon = null,
        public ?string $iconRight = null,
        public ?string $hint = null,
        public ?string $hintClass = 'fieldset-label',
        public ?bool $inline = false,
        public ?bool $clearable = false,
        public ?array $config = [],

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
            'altInputClass' => ' ',
            'clickOpens' => ! $this->attributes->has('readonly') || $this->attributes->get('readonly') == false,
            'defaultDate' => '#model#',
            'plugins' => ['#plugins#'],
            'disable' => ['#disable#'],
        ], Arr::except($this->config, ["plugins"])));

        // Plugins
        $plugins = "";

        foreach (Arr::get($this->config, 'plugins', []) as $plugin) {
            $plugins .= "new " . key($plugin) . "( " . json_encode(current($plugin)) . " ),";
        }

        $config = str_replace('"#plugins#"', $plugins, $config);

        // Disables
        $disables = '';

        foreach (Arr::get($this->config, 'disable', []) as $disable) {
            $disables .= $disable . ',';
        }

        $config = str_replace('"#disable#"', $disables, $config);

        // Sets default date as current bound model
        $config = str_replace('"#model#"', '$wire.get("' . $this->modelName() . '")', $config);

        return $config;
    }

    public function render(): View|Closure|string
    {
        return <<<'BLADE'
            <div wire:key="datepicker-{{ rand() }}">
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
                            <span class="font-semibold">{{ $label }}</span>
                        @endif

                        <div 
                            @click.outside = "clear()"
                            @keyup.esc = "clear()"

                            x-data="{
                                instance: undefined,
                                value: @entangle($attributes->wire('model')),
                                isRange: {{ json_encode(isset($config['mode']) && $config['mode'] == 'range') }},
                                focused: false,
                                isLive: {{ json_encode($attributes->wire('model')->hasModifier('live')) }},

                                init() {
                                    $watch('value', value => { 
                                        if (value.split(this.instance.l10n.rangeSeparator).length == 2 && this.isLive) { 
                                            $wire.set('{{ $modelName() }}', value) 
                                        }
                                        
                                        if (value !== '' || !this.isLive) {
                                            return
                                        }
                                        
                                        if (this.isRange) {
                                            $wire.set('{{ $modelName() }}', '')
                                            this.instance.close()
                                        } else {
                                            this.instance.close()
                                        }
                                    })
                                },
                                get isValueEmpty() {
                                    return this.value == ''
                                },
                                clear() {
                                    this.focused = false
                                },
                                reset() {
                                    this.clear()
                                    this.value = ''
                                    this.instance.clear()
                                    this.instance.close()
                                },
                                focus() {
                                    if (this.isReadonly || this.isDisabled) {
                                        return
                                    }

                                    this.focused = true
                                },
                            }"

                            @class(["w-full", "join" => $prepend || $append])
                        >
                            {{-- PREPEND --}}
                            @if($prepend)
                                {{ $prepend }}
                            @endif

                            {{-- THE LABEL THAT HOLDS THE INPUT --}}
                            <label
                                @if($isDisabled())
                                    disabled
                                @endif

                                @if(!$isDisabled() && !$isReadonly())
                                    @click="focus()"
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
                                {{-- ICON LEFT --}}
                                @if($icon)
                                    <x-mary-icon :name="$icon" class="pointer-events-none w-4 h-4 -ml-1 opacity-40" />
                                @endif

                                {{-- PLACEHOLDER --}}
                                <span :class="(focused || !isValueEmpty || !isRange || !isLive) && 'hidden'" class="text-base-content/40">
                                    {{ $attributes->get('placeholder') }}
                                </span>

                                {{-- INPUT --}}
                                <div 
                                    x-init="instance = flatpickr($refs.input, {{ $setup() }});"
                                    x-on:livewire:navigating.window="instance.destroy();"
                                    class="w-full"
                                >
                                    <input x-ref="input" {{ $attributes->merge(['type' => 'date']) }} />
                                </div>

                                {{-- CLEAR ICON --}}
                                @if($clearable)
                                    <x-mary-icon @click.prevent="reset()" x-show="!isValueEmpty" name="o-x-mark" class="cursor-pointer w-4 h-4 opacity-40"/>
                                @endif

                                {{-- ICON RIGHT --}}
                                @if($iconRight)
                                    <x-mary-icon :name="$iconRight" class="pointer-events-none w-4 h-4 opacity-40" />
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
