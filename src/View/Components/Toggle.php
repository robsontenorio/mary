<?php

namespace Mary\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Toggle extends Component
{
    public string $uuid;

    public function __construct(
        public ?string $id = null,
        public ?string $label = null,
        public ?bool $right = false,
        public ?string $hint = null,
        public ?string $hintClass = 'fieldset-label',

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
                <fieldset class="fieldset">
                    <div class="w-full">
                        <label @class(["flex gap-3 items-center cursor-pointer", "justify-between" => $right, "!items-start" => $hint])>

                            {{-- TOGGLE --}}
                            <input
                                id="{{ $uuid }}"
                                type="checkbox"

                                {{
                                    $attributes->whereDoesntStartWith('id')
                                        ->class(["order-2" => $right])
                                        ->merge(['class' => 'toggle'])
                                }}
                            />

                            {{-- LABEL --}}
                             <div @class(["order-1" => $right])>
                                <div class="text-sm font-medium">
                                    {{ $label }}

                                    @if($attributes->get('required'))
                                        <span class="text-error">*</span>
                                    @endif
                                </div>

                                {{-- HINT --}}
                                @if($hint)
                                    <div class="{{ $hintClass }}" x-classes="fieldset-label">{{ $hint }}</div>
                                @endif
                            </div>
                        </label>
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
                </fieldset>
            </div>
            BLADE;
    }
}
