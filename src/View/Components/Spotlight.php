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
        public ?string $url = '/mary/spotlight',

        // Slots
        public mixed $append = null
    ) {
        $this->uuid = "mary" . md5(serialize($this));
    }

    public function render(): View|Closure|string
    {
        return <<<'HTML'
                <div
                    x-data="
                        {
                            value: '',
                            results: [],
                            open: $persist(false).as('mary-spotlight-modal'),
                            elapsed: 0,
                            elapsedStep: 200,
                            elapsedMax: 500,
                            maxDebounce: 250,
                            searchTimer: null,
                            debounceTimer: null,
                            controller: new AbortController(),
                            query: '',
                            searchedWithNoResults: false,
                            init(){
                                if(this.open) {
                                    this.show()
                                }

                                this.$watch('value', value => this.debounce(() => this.search(), this.maxDebounce))

                                $refs.marySpotlightRef.querySelector('.modal-box').classList.add('absolute', 'top-0', 'lg:top-10', 'w-full', 'lg:max-w-3xl')

                                // Fix weird issue when navigating back
                                document.addEventListener('livewire:navigating', () => {
                                    this.close()
                                    document.querySelectorAll('.mary-spotlight-element')?.forEach(el =>  el.remove());
                                });
                            },
                            close() {
                                this.open = false
                                $refs.marySpotlightRef?.close()
                            },
                            show() {
                                this.open = true;
                                $refs.marySpotlightRef.showModal();
                            },
                            focus() {
                                $refs.spotSearch.focus();
                                $refs.spotSearch.select();
                            },
                            updateQuery(query){
                                this.query = query
                                this.search()
                            },
                            startTimer() {
                                this.searchTimer = setInterval(() => this.elapsed += this.elapsedStep, this.elapsedStep)
                            },
                            resetTimer() {
                                clearInterval(this.searchTimer)
                                this.elapsed = 0
                            },
                            debounce(fn, waitTime) {
                                clearTimeout(this.debounceTimer)
                                this.debounceTimer = setTimeout(() => fn(), waitTime)
                            },
                            async search() {
                                $refs.spotSearch.focus()

                                if (this.value == '') {
                                    this.results = []
                                    return
                                }

                                this.resetTimer()
                                this.startTimer()

                                try {
                                    this.controller?.abort()
                                    this.controller = new AbortController();

                                    let response = await fetch(`{{$url}}?search=${this.value}&${this.query}`, { signal: this.controller.signal })
                                    this.results = await response.json()
                                } catch(e) {
                                    console.log(e)
                                    return
                                }

                                this.resetTimer()

                                Object.keys(this.results).length
                                    ? this.searchedWithNoResults = false
                                    : this.searchedWithNoResults = true
                            }
                        }"

                    @keydown.window.prevent.{{ $shortcut }}="show(); focus();"
                    @keydown.escape="close()"
                    @keydown.up="$focus.previous()"
                    @keydown.down="$focus.next()"
                    @mary-search.window="updateQuery(event.detail)"
                    @mary-search-open.window="show(); focus();"
                >
                    <x-mary-modal id="marySpotlight" x-ref="marySpotlightRef" class="backdrop-blur-sm">
                        <div class="-mx-5 -mt-5 -mb-10" @click.outside="close()" @keydown.enter="close()">
                            <!-- INPUT -->
                            <div class="flex">
                                <div class="flex-1">
                                    <x-mary-input
                                        x-model="value"
                                        x-ref="spotSearch"
                                        placeholder=" {{ $searchText }}"
                                        icon="o-magnifying-glass"
                                        class="border-none focus:outline-0"
                                        tabindex="0"
                                        @focus="$el.focus()"
                                    />
                                </div>

                                @if($append)
                                    {{ $append }}
                                @endif
                            </div>

                            <!-- PROGRESS  -->
                            <div class="h-[2px] border-t-[1px] border-t-base-200 dark:border-t-base-300">
                                <progress class="progress hidden h-[1px]" :class="elapsed > elapsedMax && '!h-[2px] !block'"></progress>
                            </div>

                            <!-- SLOT -->
                            @if($slot)
                                {{ $slot }}
                            @endif

                            <!-- NO RESULTS -->
                            <template x-if="searchedWithNoResults">
                                <div class="text-gray-400 p-3 mary-spotlight-element">{{ $noResultsText }}</div>
                            </template>

                            <!-- RESULTS  -->
                            <div class="-mx-1 mt-1.5 mb-10" @click="close()">
                                <template x-for="(item, index) in results" :key="index">
                                    <!-- ITEM -->
                                    <a x-bind:href="item.link" class="mary-spotlight-element" wire:navigate tabindex="0">
                                        <div class="p-3 hover:bg-base-200 border border-t-0 border-base-200 dark:border-base-300" >
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
                                </template>
                            </div>
                        </div>
                    </x-modal>
                </div>
            HTML;
    }
}
