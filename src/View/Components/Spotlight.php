<?php

namespace Mary\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Spotlight extends Component
{
    public string $uuid;

    public function __construct(
        public ?string $id = null,
        public ?string $shortcut = "meta.g",
        public ?string $alternativeShortcut = "ctrl.g",
        public ?string $searchText = "Search ...",
        public ?string $noResultsText = "Nothing found.",
        public ?string $url = null,
        public ?string $fallbackAvatar = null,
        public ?bool $noWireNavigate = false,

        // Slots
        public mixed $append = null
    ) {
        $this->uuid = "mary" . md5(serialize($this)) . $id;
        $this->url = $this->url ?? route('mary.spotlight', absolute: false);
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
                                setTimeout(() => {
                                    this.$refs.spotSearch.focus();
                                    this.$refs.spotSearch.select();
                                }, 100)
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
                    @keydown.window.prevent.{{ $alternativeShortcut }}="show(); focus();"
                    @keydown.escape="close()"
                    @keydown.up="$focus.previous()"
                    @keydown.down="$focus.next()"
                    @mary-search.window="updateQuery(event.detail)"
                    @mary-search-open.window="show(); focus();"
                >
                    <x-mary-modal
                        id="marySpotlight"
                        x-ref="marySpotlightRef"
                        class="backdrop-blur-sm"
                        box-class="absolute py-0 top-0 lg:top-10 w-full lg:max-w-3xl rounded-none md:rounded-box"
                    >
                        <div  @click.outside="close()">
                            <!-- INPUT -->
                            <div class="flex">
                                <div class="flex-1">
                                    <div class="flex items-center">
                                        <x-mary-icon name="o-magnifying-glass"  class="opacity-40" />
                                        <input
                                            id="{{ $uuid }}"
                                            x-model="value"
                                            x-ref="spotSearch"
                                            name="spotSearch"
                                            placeholder=" {{ $searchText }}"
                                            class="w-full input my-2 border-none outline-none shadow-none border-transparent  focus:shadow-none focus:outline-none focus:border-transparent"
                                            @focus="$el.focus()"
                                            autofocus
                                            tabindex="1"
                                        />
                                    </div>
                                </div>

                                @if($append)
                                    {{ $append }}
                                @endif
                            </div>

                            <!-- PROGRESS  -->
                            <div class="h-[1px]">
                                <progress class="progress hidden h-[1px]" :class="elapsed > elapsedMax && '!h-[2px] !block'"></progress>
                            </div>

                            <!-- SLOT -->
                            @if($slot)
                                {{ $slot }}
                            @endif

                            <!-- NO RESULTS -->
                            <template x-if="searchedWithNoResults && value != ''">
                                <div class="text-base-content/50 p-3 border-t-[length:var(--border)] border-t-base-content/10 mary-spotlight-element">{{ $noResultsText }}</div>
                            </template>

                            <!-- RESULTS  -->
                            <div class="-mx-1 mt-1" @click="close()" @keydown.enter="close()" x-ref="spotResults">
                                <template x-for="(item, index) in results" :key="index">
                                    <!-- ITEM -->
                                    <a x-bind:href="item.link" class="mary-spotlight-element" @if(!$noWireNavigate) wire:navigate @endif tabindex="0">
                                        <div class="p-3 hover:bg-base-200 border-t-[length:var(--border)] border-t-base-content/10" >
                                            <div class="flex gap-3 items-center">
                                                <!-- ICON -->
                                                <template x-if="item.icon">
                                                    <div x-html="item.icon"></div>
                                                </template>
                                                <!-- AVATAR -->
                                                <template x-if="item.avatar && !item.icon">
                                                    <div>
                                                        <img :src="item.avatar" class="rounded-full w-11 h-11" @if($fallbackAvatar) onerror="this.src='{{ $fallbackAvatar }}'" @endif />
                                                    </div>
                                                </template>
                                                <div class="flex-1 overflow-hidden whitespace-nowrap text-ellipsis truncate w-0 mary-hideable">
                                                    <!-- NAME -->
                                                    <div x-html="item.name" class="font-semibold truncate"></div>

                                                    <!-- DESCRIPTION -->
                                                    <template x-if="item.description">
                                                        <div x-html="item.description" class="text-base-content/50 text-sm truncate"></div>
                                                    </template>
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                </template>
                                <div x-show="results.length" class="mb-3"></div>
                            </div>
                        </div>
                    </x-modal>
                </div>
            HTML;
    }
}
