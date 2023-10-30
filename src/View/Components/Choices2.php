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
        public ?bool $searchable = false,
        public ?bool $multiple = false,
        public Collection|array $options = new Collection(),
        public ?string $noResultText = null,

        // slots
        public mixed $item = null

    ) {
        $this->uuid = md5(serialize($this));
    }

    public function render(): View|Closure|string
    {
        return <<<'HTML'
                <div
                    x-data="{
                        selection: @entangle($attributes->wire('model')),
                        options: {{ json_encode($options) }},
                        multiple: {{ json_encode($multiple) }},
                        searchable: {{ json_encode($searchable) }},
                        focused: false,
                        toggle(id) {
                            if (this.multiple) {
                                this.selection.includes(id)
                                    ? this.selection = this.selection.filter(i => i !== id)
                                    : this.selection.push(id)
                            } else {
                                this.selection == id
                                    ? this.selection = null
                                    : this.selection = id

                                this.focused = false
                            }
                        },
                        isActive(id) {
                            return this.multiple
                                ? this.selection.includes(id)
                                : this.selection == id
                        },
                        clear() {
                            this.focused = false;
                            $refs.searchInput.value = ''
                        },
                        pull() {
                            if (this.multiple && $refs.searchInput.value == '') {
                                const trash = this.selectedOptions.slice(-1)[0]
                                this.selection = this.selection.filter(i => i !== trash.id)
                            }
                        },
                        get selectedOptions() {
                            console.log(this.selection)
                            return this.multiple
                                ? this.options.filter(i => this.selection.includes(i.id))
                                : this.options.filter(i => i.id == this.selection)
                        }
                    }"

                    @click.outside = "clear()"
                    @keyup.esc = "clear()"
                >
                    <!-- MAIN CONTAINER -->
                    <div @click="$refs.searchInput.focus(); focused = true" class="select select-primary w-full h-fit py-2 inline-block">

                        <!-- SELECTED OPTIONS -->
                        <span wire:key="choices-item-{{ rand() }}" class="break-all">
                            <template x-for="(option, index) in selectedOptions" :key="option.id">
                                <span
                                    @click="toggle(option.id)"
                                    class="bg-primary/5 hover:bg-base-300 p-1 mr-2 rounded text-primary pl-2"
                                    :class="(focused && $refs.searchInput.value == '' && index == selectedOptions.length - 1) && 'bg-primary/10'"
                                >
                                    <span x-text="option.name"></span>
                                    <x-icon name="o-x-mark" class="w-3 h-3" />
                                </span>
                            </template>
                        </span>

                        <!-- INPUT SEARCH -->
                        <input
                            x-ref="searchInput"
                            :readonly="!searchable"
                            @keydown.backspace="pull()"
                            class="outline-none bg-transparent"

                            @if($searchable)
                                wire:model.live.debounce="searchTerm"
                            @endif
                         />
                    </div>

                    <!-- OPTIONS LIST -->
                    <div x-show="focused" class="relative">
                        <div class="max-h-64 w-full absolute z-10 shadow-xl bg-base-100 border border-base-300 rounded-lg cursor-pointer overflow-y-auto">
                            @foreach($options as $option)
                                <div
                                    wire:key="choices-list-{{ $option->id }}"
                                    :class="isActive({{ $option->id }}) && 'bg-primary/5'"
                                    @click="toggle({{ $option->id }})"
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
                </div>
            HTML;
    }
}
