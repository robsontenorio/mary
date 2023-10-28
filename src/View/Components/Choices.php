<?php

namespace Mary\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\View\Component;

class Choices extends Component
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
        public ?string $noResultText = null,
        public Collection|array $options = new Collection(),

        // slots
        public mixed $item = null
    ) {
        $this->uuid = md5(serialize($this));
    }

    public function modelName()
    {
        return $this->attributes->wire('model')->value();
    }

    public function render(): View|Closure|string
    {
        return <<<'HTML'
            <div
                x-data="{
                    open: false,
                    focused: false,
                    items: [],
                    selection: @entangle($attributes->wire('model')),
                    get selectedItems() {
                        @if ($single)
                            return this.items.filter(i => i.{{ $optionValue }} == this.selection);
                        @else
                            return this.items.filter(i => this.selection.includes(i.{{ $optionValue }}));
                        @endif
                    }
                }"
            >

                <!-- STANDARD LABEL -->
                @if($label)
                    <label class="pt-0 label label-text font-semibold">{{ $label }}</label>
                @endif

                <div
                    x-data="
                    {
                        options: @js($options),
                        toggle(option) {
                            value = option.{{ $optionValue }};

                            @if($single)
                                this.items = [];
                                this.items.push(option);
                                this.selection = value;
                            @else
                                if(this.selection.includes(value)){
                                    this.items = this.items.filter(i => i.{{ $optionValue }} !== value);
                                    this.selection = this.selection.filter(i => i !== value);
                                }else{
                                    this.items.push(option);
                                    this.selection.push(value);
                                }
                            @endif
                        }
                    }"

                    x-init="
                            @if ($single)
                                items = options.filter(i => i.{{ $optionValue }} == selection);
                            @else
                                items = options.filter(i => selection.includes(i.{{ $optionValue }}));
                            @endif
                    "

                    @click.outside="open = false"
                    class="relative"
                >

                    <!-- DISPLAY SELECTION + INPUT  -->
                    <div @click="$refs.inputSearch.focus(); open = true" class="select select-primary py-2 w-full h-fit inline-block">

                       <!-- DISPLAY SELECTION  -->
                       <span @if($single && $searchable) x-show="!focused" @endif x-transition class="break-all">
                            <template x-for="item in selectedItems" :key="item.{{ $optionValue }}">
                                <span
                                    x-text="item.{{ $optionLabel}}"
                                    class="bg-base-200 hover:bg-base-300 rounded px-2 py-1 mr-2 font-semibold text-sm cursor-pointer"

                                    @if(!$single)
                                        @click.stop="toggle(item)"
                                    @endif
                                >
                                </span>
                            </template>
                        </span>

                        <!-- INPUT -->
                        <input
                            x-transition
                            x-ref="inputSearch"
                            @focus="focused = true"
                            @blur="focused = false; $el.value = ''"
                            @class([
                                    "outline-none bg-transparent",
                                    "hidden" => !$searchable
                                ])

                            @if(!$searchable)
                                readonly
                            @else
                                wire:keydown.debounce="{{ $searchFunction }}($el.value);"
                            @endif
                        />
                    </div>

                    <!-- OPTIONS CONTAINER -->
                    <div x-show="open" class="relative" wire:key="options-container">

                        <!-- PROGRESS -->
                        <progress wire:loading.delay wire:target="{{ $searchFunction }}" class="progress absolute progress-primary top-1 h-0.5"></progress>

                        <!-- OPTIONS -->
                        @if(count($options) || $noResultText)
                            <div class="absolute w-full bg-base-100 mt-2 z-10 pb-0.5 border border-base-300 shadow-xl cursor-pointer rounded-lg overflow-y-auto max-h-64">
                                @foreach($options as $option)
                                    <div
                                        wire:key="option-{{ data_get($option, $optionValue) }}"
                                        @click="
                                            $refs.inputSearch.value = '';
                                            toggle({{ json_encode($option) }});

                                            @if($single)
                                                open = false;
                                            @endif

                                            @if($searchable && !$single)
                                                $refs.inputSearch.focus();
                                            @endif
                                        "
                                        :class="
                                            @if($single)
                                                selection == '{{ data_get($option, $optionValue) }}' && 'bg-primary/5'
                                            @else
                                                selection.includes({{ data_get($option, $optionValue) }}) && 'bg-primary/5'
                                            @endif
                                        "
                                    >
                                        <!-- ITEM SLOT -->
                                        @if($item)
                                            {{ $item($option) }}
                                        @else
                                            <x-list-item :item="$option" :value="$optionLabel" :sub-value="$optionSubLabel" :avatar="$optionAvatar"  />
                                        @endif
                                    </div>
                                @endforeach
                                @if(count($options) == 0 && $noResultText)
                                    <div class="p-3">{{ $noResultText }}</div>
                                @endif
                            </div>
                        @endif
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
