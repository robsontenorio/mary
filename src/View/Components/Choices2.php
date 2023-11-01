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
        public ?bool $multiple = false,
        public ?string $searchFunction = 'search',
        public ?string $optionValue = 'id',
        public ?string $optionLabel = 'name',
        public ?string $optionSubLabel = 'description',
        public ?string $optionAvatar = 'avatar',
        public Collection|array $options = new Collection(),
        public ?string $noResultText = null,

        // slots
        public mixed $item = null

    ) {
        $this->uuid = md5(serialize($this));
    }

    public function modelName()
    {
        return $this->attributes->wire('model')->value();
    }

    public function isReadonly()
    {
        return $this->attributes->has('readonly') && $this->attributes->get('readonly') == true;
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
                        isMultiple: {{ json_encode($multiple) }},
                        isSearchable: {{ json_encode($searchable) }},
                        isReadonly: {{ json_encode($isReadonly()) }},

                        get selectedOptions() {
                            return this.isMultiple
                                ? this.selection.map(i => this.options.filter(o => o.{{ $optionValue }} == i)[0])
                                : this.options.filter(i => i.{{ $optionValue }} == this.selection)
                        },
                        clear() {
                            this.focused = false;
                            $refs.searchInput.value = ''
                        },
                        focus() {
                            if (this.isReadonly) {
                                return
                            }

                            this.focused = true
                        },
                        isActive(id) {
                            return this.isMultiple
                                ? this.selection.includes(id)
                                : this.selection == id
                        },
                        toggle(id) {
                            if (this.isReadonly) {
                                return
                            }

                            if (this.isMultiple) {
                                this.selection.includes(id)
                                    ? this.selection = this.selection.filter(i => i !== id)
                                    : this.selection.push(id)
                            } else {
                                this.selection = id
                                this.focused = false
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
                        @click="$refs.searchInput.focus(); focus()"

                        {{
                            $attributes->except('wire:model')->class([
                                "select select-bordered select-primary w-full h-fit pb-2 pt-2.5 inline-block cursor-auto relative",
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

                        <!-- SELECTED OPTIONS -->
                        <span wire:key="selected-options-{{ rand() }}" class="break-all">
                            <template x-for="(option, index) in selectedOptions" :key="option.{{ $optionValue }}">
                                <span class="bg-primary/5 text-primary hover:bg-primary/10 dark:bg-primary/20 dark:hover:bg-primary/40 dark:text-inherit p-1 mr-2 rounded  pl-2 cursor-pointer">
                                    <span x-text="option.{{ $optionLabel }}"></span>
                                    <x-icon @click="toggle(option.{{ $optionValue }})" x-show="! isReadonly && isMultiple" name="o-x-mark" class="w-4 h-4 text-gray-500 hover:text-red-500" />
                                </span>
                            </template>
                        </span>

                        <!-- INPUT SEARCH -->
                        <input
                            x-ref="searchInput"
                            class="outline-none bg-transparent"
                            :readonly="isReadonly || ! isSearchable"

                            @if($searchable)
                                wire:keydown.debounce="{{ $searchFunction }}($el.value)"
                            @endif
                         />
                    </div>

                    <!-- OPTIONS LIST -->
                    <div x-show="focused" class="relative" wire:key="options-list">
                        <div class="max-h-64 w-full absolute z-10 shadow-xl bg-base-100 border border-base-300 rounded-lg cursor-pointer overflow-y-auto">
                            @foreach($options as $option)
                                <div
                                    wire:key="option-{{ data_get($option, $optionValue) }}"
                                    @click="toggle({{ data_get($option, $optionValue) }})"
                                    :class="isActive({{ data_get($option, $optionValue) }}) && 'hidden'"
                                >
                                    <!-- ITEM SLOT -->
                                    @if($item)
                                        {{ $item($option) }}
                                    @else
                                        <x-list-item :item="$option" />
                                    @endif
                                </div>
                            @endforeach

                            <!-- NO RESULTS -->
                            @if(count($options) == 0 && $noResultText)
                                <div class="p-5">{{ $noResultText }}</div>
                            @endif
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
