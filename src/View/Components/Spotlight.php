<?php

namespace Mary\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Spotlight extends Component
{
    public string $uuid;

    public function __construct(
        public ?string $shortcut = "meta.g",
        public ?string $searchText = "Search ...",
        public ?string $noResultsText = "Nothing found.",

        // Slots
        public mixed $item = null

    ) {
        $this->uuid = md5(serialize($this));
    }

    public function render(): View|Closure|string
    {
        return <<<'HTML'
                <div
                    x-data="
                        {
                            value: '',
                            results: [],
                            searching: false,
                            searchedWithNoResults: false,
                            init(){
                                $refs.marySpotlightRef.querySelector('.modal-box').classList.add('absolute', 'top-0', 'lg:top-10', 'w-full', 'lg:max-w-3xl')

                                // Fix weird issue when navigating back
                                document.addEventListener('livewire:navigating', () => {
                                    $refs.marySpotlightRef?.close()
                                    document.querySelectorAll('.mary-spotlight-element')?.forEach(el =>  el.remove());
                                });
                            },
                            show() {
                                $refs.marySpotlightRef.showModal();
                                $refs.spotSearch.focus();
                                $refs.spotSearch.select()
                            },
                            async search() {
                                $refs.spotSearch.focus()

                                if (!this.value) {
                                    this.results = []
                                    return
                                }

                                this.searching = true
                                let response = await fetch(`/mary/spotlight?q=${this.value}`)
                                this.results = await response.json()
                                this.searching = false

                                if(Object.keys(this.results).length){
                                    this.searchedWithNoResults = false
                                }else{
                                    this.searchedWithNoResults = true
                                }
                            }
                        }"

                    @keydown.window.prevent.{{ $shortcut }}="show()"
                    @keydown.up="$focus.previous()"
                    @keydown.down="$focus.next()"
                >
                    <x-modal id="marySpotlight" x-ref="marySpotlightRef" class="backdrop-blur-sm">
                        <div class="-mx-5 -my-10">
                            <!-- INPUT -->
                            <x-mary-input
                                x-model="value"
                                x-ref="spotSearch"
                                placeholder=" {{ $searchText }}"
                                icon="o-magnifying-glass"
                                class="border-none focus:outline-0"
                                tabindex="0"
                                @focus="$el.focus()"
                                @keyup.debounce="search()"
                            />

                            <!-- PROGRESS  -->
                            <div class="h-[2px] border border-t-base-100">
                                <progress class="progress progress-primary hidden h-[1px]" :class="searching && '!h-[2px] !block'"></progress>
                            </div>

                            <!-- NO RESULTS -->
                            <template x-if="searchedWithNoResults ">
                                <div class="text-gray-400 p-3">{{ $noResultsText }}</div>
                            </template>

                            <!-- RESULTS  -->
                            <div class="-mx-1 mt-1.5 mb-10">
                                <template x-for="(item, index) in results" :key="index">
                                        <!-- ITEM SLOT -->
                                        @if($item)
                                            {{ $item }}
                                        @else
                                            <a x-bind:href="item.link" class="mary-spotlight-element" wire:navigate tabindex="0">
                                                <div class="p-3 hover:bg-base-200 border border-t-0 border-b-base-200" >
                                                    <div class="flex gap-3 items-center">
                                                        <!-- AVATAR -->
                                                        <template x-if="item.icon">
                                                            <div x-html="item.icon"></div>
                                                        </template>
                                                        <!-- AVATAR -->
                                                        <template x-if="item.avatar && !item.icon">
                                                            <div>
                                                                <img :src="item.avatar" class="rounded-full w-11 h-11" />
                                                            </div>
                                                        </template>
                                                        <div class="flex-1 overflow-hidden whitespace-nowrap text-ellipsis truncate w-0 mary-hideable">
                                                            <!-- NAME -->
                                                            <div x-text="item.name" class="font-semibold truncate"></div>

                                                            <!-- DESCRIPTION -->
                                                            <template x-if="item.description">
                                                                <div x-text="item.description" class="text-gray-400 text-sm truncate"></div>
                                                            </template>
                                                        </div>
                                                    </div>
                                                </div>
                                            </a>
                                        @endif
                                </template>
                            </div>
                        </div>
                    </x-modal>
                </div>
            HTML;
    }
}
