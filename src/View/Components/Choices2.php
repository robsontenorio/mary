<?php

namespace Mary\View\Components;

use Closure;
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
        public ?string $searchFunction = 'search',
        public ?string $optionValue = 'id',
        public ?string $optionLabel = 'name',
        public ?string $optionSubLabel = 'description',
        public ?string $optionAvatar = 'avatar',
        public Collection|array $options = new Collection(),
        public ?string $noResultText = 'No results found.',

        // slots
        public mixed $item = null

    ) {
        $this->uuid = md5(serialize($this));
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

    public function render(): View|Closure|string
    {
        return <<<'HTML'
                <div
                    @click.outside = "clear()"
                    @keyup.esc = "clear()"

                    x-data="{
                        selection: @entangle($attributes->wire('model')),
                        options: {{ json_encode($options) }},
                        focused: false,
                        isSingle: {{ json_encode($single) }},
                        isSearchable: {{ json_encode($searchable) }},
                        isReadonly: {{ json_encode($isReadonly()) }},

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
                                    ? this.selection && this.options.length  == 1
                                    : this.options.length <= this.selection.length
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
                                    ? this.selection = this.selection.filter(i => i !== id)
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

                    <!-- SELECTED OPTIONS + INPUT -->
                    <div
                        @click="focus()"

                        @if($isRequired)
                            required
                        @endif

                        {{
                            $attributes->except('wire:model')->class([
                                "select select-bordered select-primary w-full h-fit pb-2 pt-2.5 pr-16 inline-block cursor-auto relative",
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
                        <x-icon @click="reset()"  name="o-x-mark" class="absolute top-1/2 right-8 -translate-y-1/2 cursor-pointer text-gray-400 hover:text-gray-600" />

                        <!-- SELECTED OPTIONS -->
                        <span wire:key="selected-options-{{ rand() }}">
                            <template x-for="(option, index) in selectedOptions" :key="option.{{ $optionValue }}">
                                <span class="bg-primary/5 text-primary hover:bg-primary/10 dark:bg-primary/20 dark:hover:bg-primary/40 dark:text-inherit p-1 px-2 mr-2 rounded cursor-pointer break-before-all">
                                    <span x-text="option.{{ $optionLabel }}"></span>
                                    <x-icon @click="toggle(option.{{ $optionValue }})" x-show="!isReadonly && !isSingle" name="o-x-mark" class="text-gray-500 hover:text-red-500" />
                                </span>
                            </template>
                        </span>

                        <!-- INPUT SEARCH -->
                        <input
                            x-ref="searchInput"
                            @input="focus()"
                            :readonly="isReadonly || ! isSearchable"
                            :class="(isReadonly || !isSearchable) && 'hidden'"
                            class="outline-none bg-transparent"

                            @if($searchable)
                                wire:keydown.debounce="{{ $searchFunction }}($el.value)"
                            @endif
                         />
                    </div>

                    <!-- OPTIONS LIST -->
                    <div x-show="focused" class="relative" wire:key="options-list">
                        <div class="max-h-64 w-full absolute z-10 shadow-xl bg-base-100 border border-base-300 rounded-lg cursor-pointer overflow-y-auto">
                            <!-- NO RESULTS -->
                            <div
                                x-show="noResults"
                                wire:key="{{ rand() }}"
                                class="p-5 decoration-wavy decoration-warning font-bold underline border border-l-4 border-l-warning border-b-base-200"
                            >
                                {{ $noResultText }}
                            </div>

                            @foreach($options as $option)
                                <div
                                    wire:key="option-{{ data_get($option, $optionValue) }}"
                                    @click="toggle({{ data_get($option, $optionValue) }})"
                                    class="border-l-4"
                                    :class="isActive({{ data_get($option, $optionValue) }}) && 'border-l-4 border-l-primary'"
                                >
                                    <!-- ITEM SLOT -->
                                    @if($item)
                                        {{ $item($option) }}
                                    @else
                                        <x-list-item :item="$option" />
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
            HTML;
    }
}
