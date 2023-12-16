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
        public ?string $icon = null,
        public ?string $hint = null,
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
        public ?string $height = 'max-h-64',
        public Collection|array $options = new Collection(),
        public ?string $noResultText = 'No results found.',

        // slots
        public mixed $item = null,
        public mixed $selection = null,
        public mixed $prepend = null,
        public mixed $append = null
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

    public function render(): View|Closure|string
    {
        return <<<'HTML'
                <div x-data="{ focused: false, selection: @entangle($attributes->wire('model')) }">
                    <div
                        @click.outside = "clear()"
                        @keyup.esc = "clear()"

                        x-data="{
                            options: {{ json_encode($options) }},
                            isSingle: {{ json_encode($single) }},
                            isSearchable: {{ json_encode($searchable) }},
                            isReadonly: {{ json_encode($isReadonly()) }},
                            isRequired: {{ json_encode($isRequired()) }},
                            minChars: {{ $minChars }},
                            search: '',

                            get selectedOptions() {
                                return this.isSingle
                                    ? this.options.filter(i => i.{{ $optionValue }} == this.selection)
                                    : this.selection.map(i => this.options.filter(o => o.{{ $optionValue }} == i)[0])
                            },
                            get searchOptions() {
                                return this.options.filter(i => i.{{ $optionLabel }}.match(new RegExp(this.search, 'i')))
                            },
                            get noResults() {
                                if (!this.isSearchable || this.search == '') {
                                    return false
                                }

                                return this.searchOptions.length == 0
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
                                this.search = ''
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
                                    this.search = ''
                                } else {
                                    this.selection.includes(id)
                                        ? this.selection = this.selection.filter(i => i != id)
                                        : this.selection.push(id)
                                }

                                $refs.searchInput.focus()
                            }
                        }"
                    >
                        <!-- STANDARD LABEL -->
                        @if($label)
                            <label for="{{ $uuid }}" class="pt-0 label label-text font-semibold">
                                <span>
                                    {{ $label }}

                                    @if($attributes->has('required'))
                                        <span class="text-error">*</span>
                                    @endif
                                </span>
                            </label>
                        @endif

                        <!-- PREPEND/APPEND CONTAINER -->
                        @if($prepend || $append)
                            <div class="flex">
                        @endif

                        <!-- PREPEND -->
                        @if($prepend)
                            <div class="rounded-l-lg flex items-center bg-base-200">
                                {{ $prepend }}
                            </div>
                        @endif

                        <!-- SELECTED OPTIONS + SEARCH INPUT -->
                        <div
                            @click="focus()"

                            {{
                                $attributes->except(['wire:model', 'wire:model.live'])->class([
                                    "select select-bordered select-primary w-full h-fit pr-16 pb-1 pt-1.5 inline-block cursor-pointer relative",
                                    'border border-dashed' => $isReadonly(),
                                    'select-error' => $errors->has($modelName()),
                                    'rounded-l-none' => $prepend,
                                    'rounded-r-none' => $append,
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
                                <x-mary-icon @click="reset()"  name="o-x-mark" class="absolute top-1/2 right-8 -translate-y-1/2 cursor-pointer text-gray-400 hover:text-gray-600" />
                            @endif

                            <!-- SELECTION SLOT (render ahead of time to make it available for custom selection slot)-->
                            @if($selection)
                                <template x-for="(option, index) in searchOptions" :key="index">
                                    <span x-bind:id="`selection-{{ $uuid}}-${option.{{ $optionValue }}}`" class="hidden">
                                        {{ $selection }}
                                    </span>
                                </template>
                            @endif

                            <!-- SELECTED OPTIONS -->
                            <span wire:key="selected-options-{{ $uuid }}">
                                @if($compact)
                                    <div class="bg-primary/5 text-primary hover:bg-primary/10 dark:bg-primary/20 dark:hover:bg-primary/40 dark:text-inherit px-2 mr-2 mt-0.5 mb-1.5 last:mr-0 rounded inline-block cursor-pointer">
                                        <span class="font-black" x-text="selectedOptions.length"></span> {{ $compactText }}
                                    </div>
                                @else
                                    <template x-for="(option, index) in selectedOptions" :key="index">
                                        <div class="bg-primary/5 text-primary hover:bg-primary/10 dark:bg-primary/20 dark:hover:bg-primary/40 dark:text-inherit px-2 mr-2 mt-0.5 mb-1.5 last:mr-0 inline-block rounded cursor-pointer">
                                            <!-- SELECTION SLOT -->
                                             @if($selection)
                                                <span x-html="document.getElementById('selection-{{ $uuid . '-\' + option.'. $optionValue }}).innerHTML"></span>
                                             @else
                                                <span x-text="option.{{ $optionLabel }}"></span>
                                             @endif

                                            <x-mary-icon @click="toggle(option.{{ $optionValue }})" x-show="!isReadonly && !isSingle" name="o-x-mark" class="text-gray-500 hover:text-red-500" />
                                        </div>
                                    </template>
                                @endif
                            </span>

                            &nbsp;

                            <!-- INPUT SEARCH -->
                            <input
                                x-ref="searchInput"
                                x-model="search"
                                @input="focus()"
                                :required="isRequired && isSelectionEmpty"
                                :readonly="isReadonly || ! isSearchable"
                                :class="(isReadonly || !isSearchable) && 'hidden'"
                                class="outline-none mt-0.5 bg-transparent"
                             />
                        </div>


                        <!-- APPEND -->
                        @if($append)
                            <div class="rounded-r-lg flex items-center bg-base-200">
                                {{ $append }}
                            </div>
                        @endif

                        <!-- END: APPEND/PREPEND CONTAINER  -->
                        @if($prepend || $append)
                            </div>
                        @endif

                        <!-- OPTIONS LIST -->
                        <div x-show="focused" class="relative" wire:key="options-list-main-{{ $uuid }}" >
                            <div wire:key="options-list-{{ $uuid }}" class="{{ $height }} w-full absolute z-10 shadow-xl bg-base-100 border border-base-300 rounded-lg cursor-pointer overflow-y-auto">

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


                                <template x-for="(option, index) in searchOptions" :key="index">
                                    <div
                                        @click="toggle(option.{{ $optionValue }})"
                                        :class="isActive(option.{{ $optionValue }}) && 'border-l-4 border-l-primary'"
                                        class="border-l-4"
                                    >
                                        <!-- ITEM SLOT -->
                                        @if($item)
                                            {{ $item }}
                                        @else
                                            <div class="p-3 hover:bg-base-200 border border-t-0 border-b-base-200">
                                                <div class="flex gap-3 items-center">
                                                    <!-- AVATAR -->

                                                    <template x-if="option.{{ $optionAvatar }}">
                                                        <div>
                                                            <img :src="option.{{ $optionAvatar }}" class="rounded-full w-11 h-11" />
                                                        </div>
                                                    </template>
                                                    <div class="flex-1 overflow-hidden whitespace-nowrap text-ellipsis truncate w-0 mary-hideable">
                                                        <!-- LABEL -->
                                                        <div x-text="option.{{ $optionLabel }}" class="font-semibold truncate"></div>

                                                        <!-- SUB LABEL -->
                                                        @if(!empty($optionSubLabel))
                                                            <div x-text="option.{{ $optionSubLabel }}" class="text-gray-400 text-sm truncate"></div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </template>
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
