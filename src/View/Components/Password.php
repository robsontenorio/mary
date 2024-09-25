<?php

namespace Mary\View\Components;

use Closure;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

/**
 * This component is a copy of Input::class modified with a 
 * input type toggle between 'password' and 'text'.
 */
class Password extends Component
{
    public string $uuid;

    public function __construct(
        public ?string $label = null,
        public ?string $icon = null,
        public ?string $iconRight = null,
        public ?string $hint = null,
        public ?string $hintClass = 'label-text-alt text-gray-400 py-1 pb-0',
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
        public ?string $errorClass = 'text-red-500 label-text-alt p-1',
        public ?bool $omitError = false,
        public ?bool $firstErrorOnly = false,
    ) {
        $this->uuid = "mary" . md5(serialize($this));

        // Cannot use a left icon when password toggle should be shown on the left side.
        if (($this->icon && !$this->right) && !$this->onlyPassword) {
            throw new Exception("Cannot use `icon` without providing `right` or `onlyPassword`.");
        }

        // Cannot use a right icon when password toggle should be shown on the right side.
        if (($this->iconRight && $this->right) && !$this->onlyPassword) {
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
        return (!$this->icon && !$this->right) && !$this->onlyPassword;
    }

    public function placeToggleRight(): bool
    {
        return (!$this->iconRight && $this->right) && !$this->onlyPassword;
    }

    public function render(): View|Closure|string
    {
        return <<<'HTML'
            <div>
                @php
                    // Wee need this extra step to support models arrays. Ex: wire:model="emails.0" , wire:model="emails.1"
                    $uuid = $uuid . $modelName()
                @endphp

                <!-- STANDARD LABEL -->
                @if($label && !$inline)
                    <label for="{{ $uuid }}" class="pt-0 label label-text font-semibold">
                        <span>
                            {{ $label }}

                            @if($attributes->get('required'))
                                <span class="text-error">*</span>
                            @endif
                        </span>
                    </label>
                @endif

                <!-- PREFIX/SUFFIX/PREPEND/APPEND CONTAINER -->
                @if($prefix || $suffix || $prepend || $append)
                    <div class="flex">
                @endif

                <!-- PREFIX / PREPEND -->
                @if($prefix || $prepend)
                    <div
                        @class([
                                "rounded-s-lg flex items-center bg-base-200",
                                "border border-primary border-e-0 px-4" => $prefix,
                                "border-0 bg-base-300" => $attributes->has('disabled') && $attributes->get('disabled') == true,
                                "border-dashed" => $attributes->has('readonly') && $attributes->get('readonly') == true,
                                "!border-error" => $errorFieldName() && $errors->has($errorFieldName()) && !$omitError
                            ])
                    >
                        {{ $prepend ?? $prefix }}
                    </div>
                @endif

                <div class="flex-1 relative" x-data="{ hidden: true }">

                    <!-- INPUT -->
                    <input
                        id="{{ $uuid }}"
                        placeholder = "{{ $attributes->whereStartsWith('placeholder')->first() }}"
                        @if ($onlyPassword) type="password" @else x-bind:type="hidden ? 'password' : 'text'" @endif

                        {{
                            $attributes
                                ->except('type')->merge()
                                ->class([
                                    'input input-primary w-full peer',
                                    'ps-10' => $icon || $placeToggleLeft(),
                                    'h-14' => ($inline),
                                    'pt-3' => ($inline && $label),
                                    'rounded-s-none' => $prefix || $prepend,
                                    'rounded-e-none' => $suffix || $append,
                                    'border border-dashed' => $attributes->has('readonly') && $attributes->get('readonly') == true,
                                    'input-error' => $errorFieldName() && $errors->has($errorFieldName()) && !$omitError
                            ])
                        }}
                    />

                    <!-- ICON / TOGGLE INPUT TYPE -->
                    @if($icon)
                        <x-mary-icon :name="$icon" class="absolute top-1/2 -translate-y-1/2 start-3 text-gray-400 pointer-events-none" />
                    @elseif($placeToggleLeft())
                        <x-button x-on:click="hidden = !hidden" class="btn-ghost btn-sm btn-circle p-0 absolute top-1/2 -translate-y-1/2 start-1.5 text-gray-400 no-animation active:focus:-translate-y-1/2">
                            <x-icon name="{{ $passwordIcon }}" x-show="hidden" /> 
                            <x-icon name="{{ $passwordVisibleIcon }}" x-show="!hidden" x-cloak class="text-primary" /> 
                        </x-button>
                    @endif

                    <!-- CLEAR ICON -->
                    @if($clearable)
                        <x-mary-icon @click="$wire.set('{{ $modelName() }}', '', {{ json_encode($attributes->wire('model')->hasModifier('live')) }})" name="o-x-mark" class="absolute top-1/2 end-3 -translate-y-1/2 cursor-pointer text-gray-400 hover:text-gray-600" />
                    @endif

                    <!-- RIGHT ICON / TOGGLE INPUT TYPE -->
                    @if($iconRight)
                        <x-mary-icon :name="$iconRight" @class(["absolute top-1/2 end-3 -translate-y-1/2 text-gray-400 pointer-events-none", "!end-10" => $clearable]) />
                    @elseif($placeToggleRight())
                        <x-button x-on:click="hidden = !hidden" @class(["btn-ghost btn-sm btn-circle p-0 absolute top-1/2 -translate-y-1/2 end-1.5 text-gray-400 no-animation active:focus:-translate-y-1/2", "!end-9" => $clearable])>
                            <x-icon name="{{ $passwordIcon }}" x-show="hidden" /> 
                            <x-icon name="{{ $passwordVisibleIcon }}" x-show="!hidden" x-cloak class="text-primary" /> 
                        </x-button>
                    @endif

                    <!-- INLINE LABEL -->
                    @if($label && $inline)
                        <label for="{{ $uuid }}" class="absolute text-gray-400 duration-300 transform -translate-y-1 scale-75 top-2 origin-left rtl:origin-right rounded px-2 peer-focus:px-2 peer-focus:text-blue-600 peer-focus:dark:text-blue-500 peer-placeholder-shown:scale-100 peer-placeholder-shown:-translate-y-1/2 peer-placeholder-shown:top-1/2 peer-focus:top-2 peer-focus:scale-75 peer-focus:-translate-y-1 @if($inline && ($icon || $placeToggleLeft())) start-9 @else start-3 @endif">
                            {{ $label }}
                        </label>
                    @endif

                </div>

                <!-- SUFFIX/APPEND -->
                @if($suffix || $append)
                     <div
                        @class([
                                "rounded-e-lg flex items-center bg-base-200",
                                "border border-primary border-s-0 px-4" => $suffix,
                                "border-0 bg-base-300" => $attributes->has('disabled') && $attributes->get('disabled') == true,
                                "border-dashed" => $attributes->has('readonly') && $attributes->get('readonly') == true,
                                "!border-error" => $errorFieldName() && $errors->has($errorFieldName()) && !$omitError
                            ])
                    >
                        {{ $append ?? $suffix }}
                    </div>
                @endif

                <!-- END: PREFIX/SUFFIX/APPEND/PREPEND CONTAINER -->
                @if($prefix || $suffix || $prepend || $append)
                    </div>
                @endif

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
