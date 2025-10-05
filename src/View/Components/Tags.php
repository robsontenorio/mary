<?php

namespace Mary\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Tags extends Component
{
    public string $uuid;

    public function __construct(
        public ?string $id = null,
        public ?string $label = null,
        public ?string $hint = null,
        public ?string $hintClass = 'fieldset-label',
        public ?string $icon = null,
        public ?string $iconRight = null,
        public ?bool $inline = false,
        public ?bool $clearable = false,
        public ?string $prefix = null,
        public ?string $suffix = null,

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

    public function isRequired(): bool
    {
        return $this->attributes->has('required') && $this->attributes->get('required') == true;
    }

    public function render(): View|Closure|string
    {
        return <<<'BLADE'
            <div x-data="{
                    tags: @entangle($attributes->wire('model')),
                    tag: null,
                    focused: false,
                    isReadonly: {{ json_encode($isReadonly()) }},
                    isRequired: {{ json_encode($isRequired()) }},
                    isDisabled: {{ json_encode($isDisabled()) }},

                    init() {
                        if (this.tags == null || !Array.isArray(this.tags)) {
                            this.tags = [];
                        }

                        // Fix weird issue when navigating back
                        document.addEventListener('livewire:navigating', () => {
                            let elements = document.querySelectorAll('.mary-tags-element');
                            elements.forEach(el =>  el.remove());
                        });
                    },
                    push() {
                        if (this.tag != '' && this.tag != null && this.tag != undefined) {
                            let tag = this.tag.toString().replace(/,/g, '').trim()

                            if (tag != '' && !this.hasTag(tag)) {
                                this.tags.push(tag)
                            }
                        }

                        this.clear()
                    },

                    hasTag(tag) {
                        var tag = this.tags.find(e => {
                            e = e.toString();
                            return e.toLowerCase() === tag.toLowerCase()
                        })
                        return tag != undefined
                    },

                    remove(index) {
                        this.tags.splice(index, 1)
                    },

                    clear() {
                        this.tag = null;
                        this.focused = false;
                    },

                    clearAll() {
                        this.tags = [];
                    },

                    focus() {
                        if (this.isReadonly || this.isDisabled) {
                            return
                        }

                        this.focused = true
                        $refs.searchInput.focus()
                    },

                    resize() {
                        $refs.searchInput.style.width = ($refs.searchInput.value.length + 1) * 0.55 + 'rem'
                    }
                }"
                @keydown.escape="clear()"
            >
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

                        <div @class(["w-full", "join" => $prepend || $append])>
                            {{-- PREPEND --}}
                            @if($prepend)
                                {{ $prepend }}
                            @endif

                            {{-- THE LABEL THAT HOLDS THE INPUT --}}
                            <label
                                @click="focus()"

                                @if($isDisabled())
                                    disabled
                                @endif

                                {{
                                    $attributes->whereStartsWith('class')->class([
                                        "input w-full h-fit pl-2.5",
                                        "join-item" => $prepend || $append,
                                        "border-dashed" => $attributes->has("readonly") && $attributes->get("readonly") == true,
                                        "!input-error" => $errorFieldName() && $errors->has($errorFieldName()) && !$omitError
                                    ])
                                }}
                             >
                                {{-- PREFIX --}}
                                @if($prefix)
                                    <span class="label">{{ $prefix }}</span>
                                @endif

                                {{-- ICON LEFT --}}
                                @if($icon)
                                    <x-mary-icon :name="$icon" class="pointer-events-none w-4 h-4 opacity-40" />
                                @endif

                                <div class="w-full py-1 min-h-9.5 content-center text-wrap">
                                    {{-- TAGS --}}
                                    <span wire:key="tags-{{ $uuid }}">
                                        <template :key="index" x-for="(tag, index) in tags">
                                            <span class="mary-tags-element cursor-pointer badge badge-soft m-0.5 !inline-block">
                                                <span x-text="tag"></span>
                                                <x-mary-icon @click="remove(index)" x-show="!isReadonly && !isDisabled" name="o-x-mark" class="w-4 h-4 mb-0.5 hover:text-error" />
                                            </span>
                                        </template>
                                    </span>

                                    {{-- PLACEHOLDER --}}
                                    <span :class="(focused || tags.length) && 'hidden'" class="text-base-content/40">
                                        {{ $attributes->get('placeholder') }}
                                    </span>

                                    {{-- INPUT --}}
                                    <input
                                        id="{{ $uuid }}"
                                        type="text"
                                        enterkeyhint="done"
                                        class="w-1 !inline-block"
                                        x-ref="searchInput"
                                        :required="isRequired"
                                        :readonly="isReadonly"
                                        :disabled="isDisabled"
                                        x-model="tag"
                                        @input="focus(); resize();"
                                        @focus="focus()"
                                        @click.outside="clear()"
                                        @keydown.enter.prevent="push()"
                                        @keyup.prevent="if (event.key === ',') { push() }"
                                    />
                                </div>

                                {{-- CLEAR ICON  --}}
                                @if($clearable && !$isReadonly() && !$isDisabled())
                                    <x-mary-icon @click="clearAll()" x-show="tags.length" name="o-x-mark" class="cursor-pointer w-4 h-4 opacity-40"/>
                                @endif

                                {{-- ICON RIGHT --}}
                                @if($iconRight)
                                    <x-mary-icon :name="$iconRight" class="pointer-events-none w-4 h-4 opacity-40" />
                                @endif

                                {{-- SUFFIX --}}
                                @if($suffix)
                                    <span class="label">{{ $suffix }}</span>
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

                    {{-- MULTIPLE --}}
                    @error($modelName().'.*')
                        @foreach ($errors->get($modelName().'.*') as $fieldErrors)
                            @foreach ($fieldErrors as $message)
                                <div class="text-error" x-classes="text-error">{{ $message }}</div>
                            @endforeach
                        @endforeach
                    @enderror

                    {{-- HINT --}}
                    @if($hint)
                        <div class="{{ $hintClass }}" x-classes="fieldset-label">{{ $hint }}</div>
                    @endif
                </fieldset>
            </div>
        BLADE;
    }
}
