<?php
namespace Mary\View\Components;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
class Textarea extends Component
{
    public string $uuid;
    public function __construct(
        public ?string $label = null,
        public ?string $hint = null,
        public ?string $hintClass = 'fieldset-label',
        public ?bool $inline = false,
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
                        <div class="w-full"> <textarea placeholder="{{ $attributes->get('placeholder') }} " {{ $attributes->merge(['id' => $uuid]) ->class([ "textarea w-full", "border-dashed" => $attributes->has("readonly") && $attributes->get("readonly") == true,  "!textarea-error" => $errorFieldName() && $errors->has($errorFieldName()) && !$omitError ]) }} >{{ $slot }}</textarea> </div>
                    </label> @if(!$omitError && $errors->has($errorFieldName())) @foreach($errors->get($errorFieldName()) as $message) @foreach(Arr::wrap($message) as $line) <div class="{{ $errorClass }}" x-class="text-error">{{ $line }}</div> @break($firstErrorOnly) @endforeach @break($firstErrorOnly) @endforeach @endif @if($hint) <div class="{{ $hintClass }}" x-classes="fieldset-label">{{ $hint }}</div> @endif
                </fieldset>
            </div>
            BLADE;
    }
}
