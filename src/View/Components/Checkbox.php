<?php

namespace Mary\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Checkbox extends Component
{
    public string $uuid;

    public function __construct(
        public ?string $id = null,
        public ?string $label = null,
        public ?string $hint = null,
        public ?string $hintClass = 'label-text-alt text-base-content/50 py-1 pb-0',

        public ?bool $right = false,
        public ?bool $tight = false,
        // Validations
        public ?string $errorField = null,
        public ?string $errorClass = 'text-error label-text-alt p-1',
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
        return <<<'HTML'
                <div>
                    <label for="{{ $uuid }}" class="flex gap-3 items-center cursor-pointer">
                        @if($right)
                            <span @class(["font-medium", "flex-1" => !$tight])>
                                {{ $label }}

                                @if($attributes->get('required'))
                                    <span class="text-error">*</span>
                                @endif
                            </span>
                        @endif
                        <div class="flex gap-2">
                            <input
                                id="{{ $uuid }}"
                                type="checkbox"
                                {{ $attributes->whereDoesntStartWith('id')->merge(['class' => 'checkbox checkbox-sm rounded-sm']) }}  />

                            @if(!$right)
                                <div>
                                    <div class="-mt-0.5 font-medium">
                                        {{ $label }}

                                        @if($attributes->get('required'))
                                            <span class="text-error">*</span>
                                        @endif
                                    </div>

                                    {{-- HINT --}}
                                    @if($hint)
                                        <div class="{{ $hintClass }}" x-classes="label-text-alt text-base-content/50 py-1 pb-0">{{ $hint }}</div>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </label>

                    {{-- HINT --}}
                    @if($hint && $right)
                        <div class="{{ $hintClass }}" x-classes="label-text-alt text-base-content/50 py-1 pb-0">{{ $hint }}</div>
                    @endif

                    {{-- ERROR --}}
                    @if(!$omitError && $errors->has($errorFieldName()))
                        @foreach($errors->get($errorFieldName()) as $message)
                            @foreach(Arr::wrap($message) as $line)
                                <div class="{{ $errorClass }}" x-classes="text-error label-text-alt p-1">{{ $line }}</div>
                                @break($firstErrorOnly)
                            @endforeach
                            @break($firstErrorOnly)
                        @endforeach
                    @endif
                </div>
        HTML;
    }
}
