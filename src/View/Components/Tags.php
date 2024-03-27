<?php

namespace Mary\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Tags extends Component
{
    public string $uuid;

    public function __construct(
        public ?string $label = null,
        public ?string $hint = null,
        public ?string $icon = null,
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

    public function isReadonly(): bool
    {
        return $this->attributes->has('readonly') && $this->attributes->get('readonly') == true;
    }

    public function isRequired(): bool
    {
        return $this->attributes->has('required') && $this->attributes->get('required') == true;
    }

    public function render(): View|Closure|string
    {
        return <<<'HTML'
            <div x-data="{
                    tags: @entangle($attributes->wire('model')),
                    tag: null,
                    focused: false,
                    isReadonly: {{ json_encode($isReadonly()) }},
                    isRequired: {{ json_encode($isRequired()) }},

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
                        if (this.isReadonly) {
                            return
                        }

                        this.focused = true
                        $refs.searchInput.focus()
                    }
                }"
                @keydown.escape="clear()"
            >
                <!-- STANDARD LABEL -->
                @if ($label)
                    <label for="{{ $uuid }}" class="pt-0 label label-text font-semibold">
                        <span>
                            {{ $label }}

                            @if($attributes->get('required'))
                                <span class="text-error">*</span>
                            @endif
                        </span>
                    </label>
                @endif

                <!-- TAGS + SEARCH INPUT -->
                <div
                    @click="focus()"

                    {{
                        $attributes->except(['wire:model', 'wire:model.live'])->class([
                            "input input-bordered input-primary w-full h-fit pr-16 pt-1.5 pb-1 min-h-[47px] inline-block cursor-pointer relative",
                            'border border-dashed' => $isReadonly(),
                            'input-error' => $errors->has($errorFieldName()) || $errors->has($errorFieldName().'*'),
                            'pl-10' => $icon,
                        ])
                    }}
                >
                    <!-- ICON  -->
                    @if($icon)
                        <x-mary-icon :name="$icon" class="absolute top-1/2 -translate-y-1/2 left-3 text-gray-400 pointer-events-none" />
                    @endif

                    <!-- CLEAR ICON  -->
                    @if(! $isReadonly())
                        <x-mary-icon @click="clearAll()" x-show="tags.length"  name="o-x-mark" class="absolute top-1/2 right-4 -translate-y-1/2 cursor-pointer text-gray-400 hover:text-gray-600" />
                    @endif

                    <!--  TAGS  -->
                    <span wire:key="tags-{{ $uuid }}">
                        <template :key="index" x-for="(tag, index) in tags">
                            <div class="mary-tags-element bg-primary/5 text-primary hover:bg-primary/10 dark:bg-primary/20 dark:hover:bg-primary/40 dark:text-inherit px-2 mr-2 mt-0.5 mb-1.5 last:mr-0 inline-block rounded cursor-pointer">
                                <span x-text="tag"></span>
                                <x-mary-icon @click="remove(index)" x-show="!isReadonly" name="o-x-mark" class="text-gray-500 hover:text-red-500" />
                            </div>
                        </template>
                    </span>

                    &nbsp;

                    <!-- INPUT -->
                    <input
                        id="{{ $uuid }}"
                        class="outline-none mt-1 bg-transparent"
                        placeholder="{{ $attributes->whereStartsWith('placeholder')->first() }}"
                        type="text"
                        enterkeyhint="done"
                        x-ref="searchInput"
                        :class="(isReadonly || !focused) && 'w-1'"
                        :required="isRequired"
                        :readonly="isReadonly"
                        x-model="tag"
                        @input="focus()"
                        @click.outside="clear()"
                        @keydown.enter.prevent="push()"
                        @keyup.prevent="if (event.key === ',') { push() }"
                    />
                </div>

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

                <!-- MULTIPLE ERROR -->
                @error($modelName().'.*')
                    <div class="label-text-alt p-1 text-red-500">{{ $message }}</div>
                @enderror

                @if ($hint)
                    <!-- HINT -->
                    <div class="label-text-alt p-1 pb-0 text-gray-400">{{ $hint }}</div>
                @endif
            </div>
        HTML;
    }
}
