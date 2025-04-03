<?php

namespace Mary\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\View\Component;

class Radio extends Component
{
    public string $uuid;

    public function __construct(
        public ?string $id = null,
        public ?string $label = null,
        public ?string $hint = null,
        public ?string $hintClass = 'fieldset-label',
        public ?string $optionValue = 'id',
        public ?string $optionLabel = 'name',
        public ?string $optionHint = 'hint',
        public Collection|array $options = new Collection(),
        public ?bool $inline = false,

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

    public function render(): View|Closure|string
    {
        return <<<'BLADE'
                <div>
                    <fieldset class="fieldset py-0">
                    {{-- STANDARD LABEL --}}
                    @if($label)
                        <legend class="fieldset-legend mb-2">
                            {{ $label }}

                            @if($attributes->get('required'))
                                <span class="text-error">*</span>
                            @endif
                        </legend>
                    @endif

                        <div @class(["gap-4 grid", "sm:flex sm:gap-6" => $inline])>
                            @foreach ($options as $option)
                                <label>
                                    <div @class(["flex items-center gap-3 cursor-pointer", "!items-start" => data_get($option, $optionHint)])>
                                        <input
                                            type="radio"
                                            name="{{ $modelName() }}"
                                            value="{{ data_get($option, $optionValue) }}"
                                            @if(data_get($option, 'disabled')) disabled @endif

                                            {{ $attributes->whereStartsWith('wire:model') }}
                                            {{ $attributes->class(["radio"]) }}
                                        />

                                        <div>
                                            {{-- NAME --}}
                                            <div class="text-sm font-medium">
                                                {{ data_get($option, $optionLabel) }}
                                            </div>

                                            {{-- HINT --}}
                                            @if(data_get($option, $optionHint))
                                                <div class="{{ $hintClass }} mt-1" x-classes="fieldset-label">
                                                    {{ data_get($option, $optionHint) }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </label>
                            @endforeach
                        </div>

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
