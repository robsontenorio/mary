<?php

namespace Mary\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\View\Component;

class SelectGroup extends Component
{
    public string $uuid;

    public function __construct(
        public ?string $label = null,
        public ?string $icon = null,
        public ?string $iconRight = null,
        public ?string $hint = null,
        public ?string $hintClass = 'fieldset-label',
        public ?string $placeholder = null,
        public ?string $placeholderValue = null,
        public ?bool $inline = false,
        public ?string $optionValue = 'id',
        public ?string $optionLabel = 'name',
        public Collection|array $options = new Collection(),

        // Slots
        public mixed $prepend = null,
        public mixed $append = null,

        // Validations
        public ?string $errorField = null,
        public ?string $errorClass = 'text-error',
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
        return <<<'BLADE'
            <div>
                @php $uuid = $uuid . $modelName() @endphp

                <fieldset class="fieldset py-0">
                    @if($label && !$inline) <legend class="fieldset-legend mb-0.5"> {{ $label }} @if($attributes->get('required')) <span class="text-error">*</span> @endif </legend> @endif
                    <label @class(["floating-label" => $label && $inline])>
                        @if ($label && $inline) <span class="font-semibold">{{ $label }}</span> @endif
                        <div @class(["w-full", "join" => $prepend || $append])> @if($prepend) {{ $prepend }} @endif
                            <label {{ $attributes->whereStartsWith('class')->class([ "select w-full", "join-item" => $prepend || $append, "border-dashed" => $attributes->has("readonly") && $attributes->get("readonly") == true, "!select-error" => $errorFieldName() && $errors->has($errorFieldName()) && !$omitError ]) }} >
                                @if($icon) <x-mary-icon :name="$icon" class="pointer-events-none w-4 h-4 -ml-1 opacity-40" /> @endif
                                <select id="{{ $uuid }}" {{ $attributes->whereDoesntStartWith('class') }}> @if($placeholder) <option value="{{ $placeholderValue }}">{{ $placeholder }}</option> @endif  @foreach (array_keys($options) as $option) <optgroup label="{{ $option }}"> @foreach($options[$option] as $opts) <option value="{{ $opts[$optionValue] }}">{{ $opts[$optionLabel] }}</option> @endforeach @endforeach </select>
                                @if($iconRight) <x-mary-icon :name="$iconRight" class="pointer-events-none w-4 h-4 opacity-40" /> @endif
                            </label> @if($append) {{ $append }} @endif
                        </div>
                    </label> @if($hint) <div class="{{ $hintClass }}" x-classes="fieldset-label">{{ $hint }}</div> @endif @if(!$omitError && $errors->has($errorFieldName())) @foreach($errors->get($errorFieldName()) as $message) @foreach(Arr::wrap($message) as $line) <div class="{{ $errorClass }}" x-class="text-error">{{ $line }}</div> @break($firstErrorOnly) @endforeach @break($firstErrorOnly) @endforeach @endif
                </fieldset>
            </div>
            BLADE;
    }
}
