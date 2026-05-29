<?php
namespace Mary\View\Components;
use Closure;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
class Password extends Component
{
    public string $uuid;
    public function __construct(
        public ?string $label = null,
        public ?string $icon = null,
        public ?string $iconRight = null,
        public ?string $hint = null,
        public ?string $hintClass = 'fieldset-label',
        public ?string $prefix = null,
        public ?string $suffix = null,
        public ?bool $inline = false,
        public ?bool $clearable = false,
        // Password
        public ?string $passwordIcon = 'o-eye-slash',
        public ?string $passwordVisibleIcon = 'o-eye',
        public ?bool $right = false,
        public ?bool $onlyPassword = false,
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
        // Cannot use a left icon when password toggle should be shown on the left side.
        if (($this->icon && ! $this->right) && ! $this->onlyPassword) {
            throw new Exception("Cannot use `icon` without providing `right` or `onlyPassword`.");
        }
        // Cannot use a right icon when password toggle should be shown on the right side.
        if (($this->iconRight && $this->right) && ! $this->onlyPassword) {
            throw new Exception("Cannot use `iconRight` when providing `right` and not providing `onlyPassword`.");
        }
    }
    public function modelName(): ?string
    {
        return $this->attributes->whereStartsWith('wire:model')->first();
    }
    public function errorFieldName(): ?string
    {
        return $this->errorField ?? $this->modelName();
    }
    public function placeToggleLeft(): bool
    {
        return (! $this->icon && ! $this->right) && ! $this->onlyPassword;
    }
    public function placeToggleRight(): bool
    {
        return (! $this->iconRight && $this->right) && ! $this->onlyPassword;
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
                        <div @class(["w-full", "join" => $prepend || $append])>
                            @if($prepend) {{ $prepend }} @endif
                            <label x-data="{ hidden: true }" {{ $attributes->whereStartsWith('class')->class([ "input w-full", "join-item" => $prepend || $append, "border-dashed" => $attributes->has("readonly") && $attributes->get("readonly") == true, "!input-error" => $errorFieldName() && $errors->has($errorFieldName()) && !$omitError ]) }} >
                                @if($prefix) <span class="label">{{ $prefix }}</span> @endif
                                @if($icon) <x-mary-icon :name="$icon" class="pointer-events-none w-4 h-4 opacity-40" /> @elseif($placeToggleLeft()) <x-mary-button x-on:click="hidden = !hidden" class="btn-ghost btn-xs btn-circle"> <x-mary-icon name="{{ $passwordIcon }}" x-show="hidden" class="w-4 h-4 opacity-40" /> <x-mary-icon name="{{ $passwordVisibleIcon }}" x-show="!hidden" x-cloak class="w-4 h-4 opacity-40" /> </x-mary-button> @endif 
                                <input id="{{ $uuid }}" placeholder="{{ $attributes->get('placeholder') }} " @if ($onlyPassword) type="password" @else x-bind:type="hidden ? 'password' : 'text'" @endif @if($attributes->has('autofocus') && $attributes->get('autofocus') == true) autofocus @endif {{ $attributes->except('type')->merge() }} />
                                @if($clearable) <x-mary-icon x-on:click="$wire.set('{{ $modelName() }}', '', {{ json_encode($attributes->wire('model')->hasModifier('live')) }})"  name="o-x-mark" class="cursor-pointer w-4 h-4 opacity-40"/> @endif
                                @if($iconRight) <x-mary-icon :name="$iconRight" @class(["pointer-events-none w-4 h-4 opacity-40", "!end-10" => $clearable]) /> @elseif($placeToggleRight()) <x-mary-button x-on:click="hidden = !hidden" @class(["btn-ghost btn-xs btn-circle", "!end-9" => $clearable])> <x-mary-icon name="{{ $passwordIcon }}" x-show="hidden" class="w-4 h-4 opacity-40" /> <x-mary-icon name="{{ $passwordVisibleIcon }}" x-show="!hidden" x-cloak class="w-4 h-4 opacity-40" /> </x-mary-button> @endif
                                @if($suffix) <span class="label">{{ $suffix }}</span> @endif
                            </label> @if($append) {{ $append }} @endif
                        </div>
                    </label>
                    @if($hint) <div class="{{ $hintClass }}" x-classes="fieldset-label">{{ $hint }}</div> @endif
                    @if(!$omitError && $errors->has($errorFieldName())) @foreach($errors->get($errorFieldName()) as $message) @foreach(Arr::wrap($message) as $line) <div class="{{ $errorClass }}" x-class="text-error">{{ $line }}</div> @break($firstErrorOnly) @endforeach @break($firstErrorOnly) @endforeach @endif
                </fieldset>
            </div>
            BLADE;
    }
}
