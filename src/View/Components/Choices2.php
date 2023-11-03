<?php

namespace Mary\View\Components;

use Closure;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\View\Component;

class Choices2 extends Component
{
    public string $uuid;

    public function __construct(
        public ?string $label = null,
        public ?string $icon = null,
        public ?string $hint = null,
        public ?bool $searchable = false,
        public ?bool $single = false,
        public ?bool $compact = false,
        public ?string $compactText = 'selected',
        public ?bool $allowAll = false,
        public ?string $allowAllText = 'Select all',
        public ?string $removeAllText = 'Remove all',
        public ?string $searchFunction = 'search',
        public ?string $optionValue = 'id',
        public ?string $optionLabel = 'name',
        public ?string $optionSubLabel = '',
        public ?string $optionAvatar = 'avatar',
        public ?string $height = 'max-h-64',
        public Collection|array $options = new Collection(),
        public ?string $noResultText = 'No results found.',

        // slots
        public mixed $item = null
    ) {
        $this->uuid = md5(serialize($this));

        if (($this->allowAll || $this->compact) && ($this->single || $this->searchable)) {
            throw new Exception("`allow-all` and `compact` does not work combined with `single` or `searchable`.");
        }
    }

    public function modelName(): string
    {
        return $this->attributes->wire('model')->value();
    }

    public function isReadonly(): bool
    {
        return $this->attributes->has('readonly') && $this->attributes->get('readonly') == true;
    }

    public function isRequired(): bool
    {
        return $this->attributes->has('required') && $this->attributes->get('required') == true;
    }

    public function getOptionValue($option): mixed
    {
        $value = data_get($option, $this->optionValue);

        return is_numeric($value) ? $value : "'$value'";
    }

    public function render(): View|Closure|string
    {
        return <<<'HTML'
                <div x-data="{focused: false}">
                    <div
                        @click.outside = "clear()"
                        @keyup.esc = "clear()"

                        x-data="{
                            selection: @entangle($attributes->wire('model')),
                            options: {{ json_encode($options) }},
                            isSingle: {{ json_encode($single) }},
                            isSearchable: {{ json_encode($searchable) }},
                            isReadonly: {{ json_encode($isReadonly()) }},
                            isRequired: {{ json_encode($isRequired()) }},

                            get selectedOptions() {
                                return this.isSingle
                                    ? this.options.filter(i => i.{{ $optionValue }} == this.selection)
                                    : this.selection.map(i => this.options.filter(o => o.{{ $optionValue }} == i)[0])
                            },
                            get noResults() {
                                if (!this.isSearchable || $refs.searchInput.value == '') {
                                    return false
                                }

                                return this.isSingle
                                        ? (this.selection && this.options.length  == 1) || (!this.selection && this.options.length == 0)
                                        : this.options.length <= this.selection.length
                            },
                            get isAllSelected() {
                                return this.options.length == this.selection.length
                            },
                            get isSelectionEmpty() {
                                return this.isSingle
                                    ? this.selection == null
                                    : this.selection.length == 0
                            },
                            selectAll() {
                                this.selection = this.options.map(i => i.{{ $optionValue }})
                            },
                            clear() {
                                this.focused = false;
                                $refs.searchInput.value = ''
                            },
                            reset() {
                                this.clear();
                                this.isSingle
                                    ? this.selection = null
                                    : this.selection = []
                            },
                            focus() {
                                if (this.isReadonly) {
                                    return
                                }

                                $refs.searchInput.focus()
                                this.focused = true
                            },
                            isActive(id) {
                                return this.isSingle
                                    ? this.selection == id
                                    : this.selection.includes(id)
                            },
                            toggle(id) {
                                if (this.isReadonly) {
                                    return
                                }

                                if (this.isSingle) {
                                    this.selection = id
                                    this.focused = false
                                } else {
                                    this.selection.includes(id)
                                        ? this.selection = this.selection.filter(i => i != id)
                                        : this.selection.push(id)
                                }

                                $refs.searchInput.value = ''
                                $refs.searchInput.focus()
                            }
                        }"
                    >
                        <!-- STANDARD LABEL -->
                        @if($label)
                            <label class="pt-0 label label-text font-semibold">{{ $label }}</label>
                        @endif

                        <!-- SELECTED OPTIONS + SEARCH INPUT -->
                        <div
                            @click="focus()"

                            {{
                                $attributes->except('wire:model')->class([
                                    "select select-bordered select-primary w-full h-fit pb-2 pt-2.5 pr-16 inline-block cursor-pointer relative",
                                    'border border-dashed' => $isReadonly(),
                                    'select-error' => $errors->has($modelName()),
                                    'pl-10' => $icon,
                                ])
                            }}
                        >
                            <!-- ICON  -->
                            @if($icon)
                                <x-icon :name="$icon" class="absolute top-1/2 -translate-y-1/2 left-3 text-gray-400 pointer-events-none" />
                            @endif

                            <!-- CLEAR ICON  -->
                            @if(! $isReadonly())
                                <x-icon @click="reset()"  name="o-x-mark" class="absolute top-1/2 right-8 -translate-y-1/2 cursor-pointer text-gray-400 hover:text-gray-600" />
                            @endif

                            <!-- SELECTED OPTIONS -->
                            <span wire:key="selected-options-{{ $uuid }}">
                                @if($compact)
                                    <span class="bg-primary/5 text-primary hover:bg-primary/10 dark:bg-primary/20 dark:hover:bg-primary/40 dark:text-inherit p-1 px-2 mr-2 rounded cursor-pointer">
                                        <span class="font-black" x-text="selectedOptions.length"></span> {{ $compactText }}
                                    </span>
                                @else
                                    <template x-for="(option, index) in selectedOptions" :key="index">
                                        <span class="bg-primary/5 text-primary hover:bg-primary/10 dark:bg-primary/20 dark:hover:bg-primary/40 dark:text-inherit p-1 px-2 mr-2 rounded cursor-pointer break-before-all">
                                            <span x-text="option.{{ $optionLabel }}"></span>
                                            <x-icon @click="toggle(option.{{ $optionValue }})" x-show="!isReadonly && !isSingle" name="o-x-mark" class="text-gray-500 hover:text-red-500" />
                                        </span>
                                    </template>
                                @endif
                            </span>

                            &nbsp;

                            <!-- INPUT SEARCH -->
                            <input
                                x-ref="searchInput"
                                @input="focus()"
                                :required="isRequired && isSelectionEmpty"
                                :readonly="isReadonly || ! isSearchable"
                                :class="(isReadonly || !isSearchable) && 'hidden'"
                                class="outline-none bg-transparent"

                                @if($searchable)
                                    wire:keydown.debounce="{{ $searchFunction }}($el.value)"
                                @endif
                             />
                        </div>

                        <!-- OPTIONS LIST -->
                        <div x-show="focused" class="relative">
                            <div wire:key="options-list-{{ $uuid }}" class="{{ $height}} w-full absolute z-10 shadow-xl bg-base-100 border border-base-300 rounded-lg cursor-pointer overflow-y-auto">

                                <!-- PROGRESS -->
                                <progress wire:loading wire:target="{{ $searchFunction }}" class="progress absolute progress-primary top-0 h-0.5"></progress>

                               <!-- SELECT ALL -->
                               @if($allowAll)
                                   <div
                                        wire:key="allow-all-{{ rand() }}"
                                        class="font-bold   border border-l-4 border-b-base-200 hover:bg-base-200"
                                   >
                                        <div x-show="!isAllSelected" @click="selectAll()" class="p-3 underline decoration-wavy decoration-info">{{ $allowAllText }}</div>
                                        <div x-show="isAllSelected" @click="reset()" class="p-3 underline decoration-wavy decoration-error">{{ $removeAllText }}</div>
                                   </div>
                               @endif

                                <!-- NO RESULTS -->
                                <div
                                    x-show="noResults"
                                    wire:key="no-results-{{ rand() }}"
                                    class="p-3 decoration-wavy decoration-warning underline font-bold border border-l-4 border-l-warning border-b-base-200"
                                >
                                    {{ $noResultText }}
                                </div>

                                @foreach($options as $option)
                                    <div
                                        wire:key="option-{{ data_get($option, $optionValue) }}"
                                        @click="toggle({{ $getOptionValue($option) }})"
                                        :class="isActive({{ $getOptionValue($option) }}) && 'border-l-4 border-l-primary'"
                                        class="border-l-4"
                                    >
                                        <!-- ITEM SLOT -->
                                        @if($item)
                                            {{ $item($option) }}
                                        @else
                                            <x-list-item :item="$option" :value="$optionLabel" :sub-value="$optionSubLabel" :avatar="$optionAvatar"  />
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- ERROR -->
                        @error($modelName())
                            <div class="text-red-500 label-text-alt p-1">{{ $message }}</div>
                        @enderror

                        <!-- HINT -->
                        @if($hint)
                            <div class="label-text-alt text-gray-400 p-1 pb-0">{{ $hint }}</div>
                        @endif
                    </div>
                </div>
            HTML;
    }
}
