<?php

namespace Mary\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Toggle extends Component
{
    public string $uuid;

    public function __construct(
        public ?string $label = null,
        public ?string $hint = null,
        public ?bool $right = false,
        public ?bool $tight = false,
        public ?string $hintClass = 'label-text-alt text-gray-400 py-1 pb-0',
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
                    <label for="{{ $uuid }}" class="flex items-center gap-3 cursor-pointer">

                        @if($right)
                            <span @class(["flex-1" => !$tight])>
                                {{ $label}}
                                
                                @if($attributes->get('required'))
                                    <span class="text-error">*</span>
                                @endif
                            </span>
                        @endif

                        <input id="{{ $uuid }}" type="checkbox" {{ $attributes->whereDoesntStartWith('class') }} {{ $attributes->class(['toggle toggle-primary']) }}  />

                        @if(!$right)
                            {{ $label}}
                            
                            @if($attributes->get('required'))
                                <span class="text-error">*</span>
                            @endif
                        @endif
                    </label>

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
