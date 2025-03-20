<?php

namespace Mary\View\Components;

use Closure;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\View\Component;

class ChoicesOffline extends Component
{
    public string $uuid;

    public function __construct(
        public ?string $label = null,
        public ?string $hint = null,
        public ?string $hintClass = 'fieldset-label',
        public ?string $icon = null,
        public ?string $iconRight = null,
        public ?bool $inline = false,
        public ?bool $clearable = false,
        public ?string $prefix = null,
        public ?string $suffix = null,

        public ?bool $searchable = false,
        public ?bool $single = false,
        public ?bool $compact = false,
        public ?string $compactText = 'selected',
        public ?bool $allowAll = false,
        public ?string $debounce = '250ms',
        public ?int $minChars = 0,
        public ?string $allowAllText = 'Select all',
        public ?string $removeAllText = 'Remove all',
        public ?string $optionValue = 'id',
        public ?string $optionLabel = 'name',
        public ?string $optionSubLabel = '',
        public ?string $optionAvatar = 'avatar',
        public ?bool $valuesAsString = false,
        public ?string $height = 'max-h-64',
        public Collection|array $options = new Collection(),
        public ?string $noResultText = 'No results found.',

        // Validations
        public ?string $errorField = null,
        public ?string $errorClass = 'text-error label-text-alt p-1',
        public ?bool $omitError = false,
        public ?bool $firstErrorOnly = false,

        // Slots
        public mixed $item = null,
        public mixed $selection = null,
        public mixed $prepend = null,
        public mixed $append = null
    ) {
        $this->uuid = "mary" . md5(serialize($this));

        if (($this->allowAll || $this->compact) && ($this->single || $this->searchable)) {
            throw new Exception("`allow-all` and `compact` does not work combined with `single` or `searchable`.");
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

    public function getOptionValue($option): mixed
    {
        $value = data_get($option, $this->optionValue);

        if ($this->valuesAsString) {
            return "'$value'";
        }

        return is_numeric($value) && ! str($value)->startsWith('0') ? $value : "'$value'";
    }

    public function render(): View|Closure|string
    {
        return <<<'HTML'
                <div x-data="{ focused: false, selection: @entangle($attributes->wire('model')) }">
                    <div
                        @click.outside = "clear()"
                        @keyup.esc = "clear()"

                        x-data="{
                            id: $id('{{ $uuid }}'),
                            options: {{ json_encode($options) }},
                            isSingle: {{ json_encode($single) }},
                            isSearchable: {{ json_encode($searchable) }},
                            isReadonly: {{ json_encode($isReadonly()) }},
                            isDisabled: {{ json_encode($isDisabled()) }},
                            isRequired: {{ json_encode($isRequired()) }},
                            minChars: {{ $minChars }},
                            noResults: false,
                            search: '',

                            init() {
                                // Fix weird issue when navigating back
                                document.addEventListener('livewire:navigating', () => {
                                    let elements = document.querySelectorAll('.mary-choices-element');
                                    elements.forEach(el =>  el.remove());
                                });
                            },
                            get selectedOptions() {
                                return this.isSingle
                                    ? this.options.filter(i => i.{{ $optionValue }} == this.selection)
                                    : this.selection.map(i => this.options.filter(o => o.{{ $optionValue }} == i)[0])
                            },
                            get isAllSelected() {
                                return this.options.length == this.selection.length
                            },
                            get isSelectionEmpty() {
                                return this.isSingle
                                    ? this.selection == null || this.selection == ''
                                    : this.selection.length == 0
                            },
                            selectAll() {
                                this.selection = this.options.map(i => i.{{ $optionValue }})
                            },
                            clear() {
                                this.focused = false;
                                this.search = ''
                            },
                            reset() {
                                this.clear();
                                this.isSingle
                                    ? this.selection = null
                                    : this.selection = []

                                this.dispatchChangeEvent({ value: this.selection })
                            },
                            focus() {
                                if (this.isReadonly || this.isDisabled) {
                                    return
                                }

                                this.focused = true
                                this.$refs.searchInput.focus()
                            },
                            resize() {
                                $refs.searchInput.style.width = ($refs.searchInput.value.length + 1) * 0.55 + 'rem'
                            },
                            isActive(id) {
                                return this.isSingle
                                    ? this.selection == id
                                    : this.selection.includes(id)
                            },
                            toggle(id, keepOpen = false) {
                                if (this.isReadonly || this.isDisabled) {
                                    return
                                }

                                if (this.isSingle) {
                                    this.selection = id
                                    this.focused = false
                                    this.search = ''
                                } else {
                                    this.selection.includes(id)
                                        ? this.selection = this.selection.filter(i => i != id)
                                        : this.selection.push(id)
                                }

                                this.dispatchChangeEvent({ value: this.selection })

                                if (!keepOpen) {
                                    this.$refs.searchInput.focus()
                                }
                            },
                            lookup() {
                                Array.from(this.$refs.choicesOptions.children).forEach(child => {
                                    if (!child.getAttribute('search-value').match(new RegExp(this.search, 'i'))){
                                        child.classList.add('hidden')
                                    } else {
                                        child.classList.remove('hidden')
                                    }
                                })

                                this.noResults = Array.from(this.$refs.choicesOptions.querySelectorAll('div > .hidden')).length ==
                                                 Array.from(this.$refs.choicesOptions.querySelectorAll('[search-value]')).length
                            },
                            dispatchChangeEvent(detail) {
                                this.$refs.searchInput.dispatchEvent(new CustomEvent('change-selection', { bubbles: true, detail }))
                            }
                        }"

                        @keydown.up="$focus.previous()"
                        @keydown.down="$focus.next()"
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
                                        x-ref="container"

                                        @if($isDisabled())
                                            disabled
                                        @endif

                                        {{
                                            $attributes->whereStartsWith('class')->class([
                                                "select w-full h-fit pl-2.5",
                                                "join-item" => $prepend || $append,
                                                "border-dashed" => $attributes->has("readonly") && $attributes->get("readonly") == true,
                                                "!select-error" => $errorFieldName() && $errors->has($errorFieldName()) && !$omitError
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

                                        <div class="w-full py-0.5 min-h-9.5 content-center text-wrap">

                                            {{-- SELECTED OPTIONS --}}
                                            <span wire:key="selected-options-{{ $uuid }}">
                                                @if($compact)
                                                    <div class="badge badge-soft">
                                                        <span class="font-black" x-text="selectedOptions.length"></span> {{ $compactText }}
                                                    </div>
                                                @else
                                                    <template x-for="(option, index) in selectedOptions" :key="index">
                                                        <span class="mary-choices-element cursor-pointer badge badge-soft m-0.5 !inline-block">
                                                            {{-- SELECTION SLOT --}}
                                                            @if($selection)
                                                                <span x-html="document.getElementById('selection-{{ $uuid . '-\' + option.'. $optionValue }}).innerHTML"></span>
                                                            @else
                                                                <span x-text="option?.{{ $optionLabel }}"></span>
                                                            @endif

                                                            <x-mary-icon @click="toggle(option.{{ $optionValue }})" x-show="!isReadonly && !isDisabled && !isSingle" name="o-x-mark" class="w-4 h-4 hover:text-error" />
                                                        </span>
                                                    </template>
                                                @endif
                                            </span>

                                            {{-- PLACEHOLDER --}}
                                            <span :class="(focused || !isSelectionEmpty) && 'hidden'" class="text-base-content/40">
                                                {{ $attributes->get('placeholder') }}
                                            </span>

                                            {{-- INPUT SEARCH --}}
                                            <input
                                                :id="id"
                                                x-ref="searchInput"
                                                x-model="search"
                                                @keyup="lookup()"
                                                @input="focus(); resize();"
                                                @focus="focus()"
                                                @keydown.arrow-down.prevent="focus()"
                                                :required="isRequired && isSelectionEmpty"
                                                :readonly="isReadonly || isDisabled || ! isSearchable"
                                                class="w-1 !inline-block outline-hidden"
                                             />
                                        </div>

                                        {{-- CLEAR ICON  --}}
                                        @if($clearable && !$isReadonly() && !$isDisabled())
                                            <x-mary-icon @click="reset()" x-show="!isSelectionEmpty" name="o-x-mark" class="cursor-pointer w-4 h-4 opacity-40"/>
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

                                {{-- HINT --}}
                                @if($hint)
                                    <div class="{{ $hintClass }}" x-classes="fieldset-label">{{ $hint }}</div>
                                @endif
                        </fieldset>

                        {{-- OPTIONS LIST --}}
                        <div x-cloak x-show="focused" class="relative" wire:key="options-list-main-{{ $uuid }}" >
                                <div
                                    wire:key="options-list-{{ $uuid }}"
                                    class="{{ $height }} w-full absolute z-10 shadow-xl bg-base-100 border border-base-content/10 rounded-lg cursor-pointer overflow-y-auto @if(!$hint) !top-1 @else !-top-5 @endif"
                                    x-anchor.bottom-start="$refs.container"
                                >

                                   {{-- SELECT ALL --}}
                                   @if($allowAll)
                                       <div
                                            wire:key="allow-all-{{ rand() }}"
                                            class="font-bold   border border-s-4 border-b-base-200 hover:bg-base-200"
                                       >
                                            <div x-show="!isAllSelected" @click="selectAll()" class="p-3 underline decoration-wavy decoration-info">{{ $allowAllText }}</div>
                                            <div x-show="isAllSelected" @click="reset()" class="p-3 underline decoration-wavy decoration-error">{{ $removeAllText }}</div>
                                       </div>
                                   @endif

                                    {{-- NO RESULTS --}}
                                    <div
                                        x-show="noResults"
                                        wire:key="no-results-{{ rand() }}"
                                        class="p-3 decoration-wavy decoration-warning underline font-bold border border-s-4 border-s-warning border-b-base-200"
                                    >
                                        {{ $noResultText }}
                                    </div>

                                    <div x-ref="choicesOptions">
                                        @foreach($options as $option)
                                            <div
                                                id="option-{{ $uuid }}-{{ data_get($option, $optionValue) }}"
                                                wire:key="option-{{ data_get($option, $optionValue) }}"
                                                @click="toggle({{ $getOptionValue($option) }}, true)"
                                                @keydown.enter="toggle({{ $getOptionValue($option) }}, true)"
                                                :class="isActive({{ $getOptionValue($option) }}) && 'border-s-4 border-s-base-content'"
                                                search-value="{{ data_get($option, $optionLabel) }}"
                                                class="border-s-4 border-base-content/10 focus:bg-base-200 focus:outline-none"
                                                tabindex="0"
                                            >
                                                {{-- ITEM SLOT --}}
                                                @if($item)
                                                    {{ $item($option) }}
                                                @else
                                                    <x-mary-list-item :item="$option" :value="$optionLabel" :sub-value="$optionSubLabel" :avatar="$optionAvatar" />
                                                @endif

                                                {{-- SELECTION SLOT --}}
                                                @if($selection)
                                                    <span id="selection-{{ $uuid }}-{{ data_get($option, $optionValue) }}" class="hidden">
                                                        {{ $selection($option) }}
                                                    </span>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                    </div>
                </div>
            HTML;
    }
}
