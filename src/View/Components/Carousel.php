<?php

namespace Mary\View\Components;

use Closure;
use Illuminate\View\Component;
use Illuminate\View\View;

/**
 * Inspired by Penguin UI.
 * Thank you.
 */
class Carousel extends Component
{
    public string $uuid;

    public function __construct(
        public array $slides,
        public ?bool $withoutIndicators = false,
        public ?bool $withoutArrows = false,

        // Slots
        public mixed $content = null,
    ) {
        $this->uuid = "mary" . md5(serialize($this));
    }

    public function render(): View|Closure|string
    {
        return <<<'HTML'
            <div x-data="{
                slides: @js($slides),
                withoutIndicators: {{ json_encode($withoutIndicators) }},
                currentSlideIndex: 1,
                touchStartX: null,
                touchEndX: null,
                swipeThreshold: 50,
                previous() {
                    this.currentSlideIndex = (this.currentSlideIndex > 1)
                        ? --this.currentSlideIndex
                        : this.slides.length
                },
                next() {
                    this.currentSlideIndex = (this.currentSlideIndex < this.slides.length)
                        ? ++this.currentSlideIndex
                        : 1
                },
                handleTouchStart(event) {
                    this.touchStartX = event.touches[0].clientX
                },
                handleTouchMove(event) {
                    this.touchEndX = event.touches[0].clientX
                },
                handleTouchEnd() {
                    if(this.touchEndX){
                        if (this.touchStartX - this.touchEndX > this.swipeThreshold) {
                            this.next()
                        }
                        if (this.touchStartX - this.touchEndX < -this.swipeThreshold) {
                            this.previous()
                        }
                        this.touchStartX = null
                        this.touchEndX = null
                    }
                },
            }" class="relative w-full overflow-hidden">

                @if(!$withoutArrows)
                    <!-- previous button -->
                    <x-mary-button icon="o-chevron-left"  @click="previous()" class="absolute cursor-pointer left-5 top-1/2 z-[2] btn-circle btn-sm" />
                    <!-- next button -->
                    <x-mary-button icon="o-chevron-right"  @click="next()" class="absolute cursor-pointer right-5 top-1/2 z-[2] btn-circle btn-sm" />
                @endif

                <!-- slides -->
                <div
                     @touchstart="handleTouchStart($event)" @touchmove="handleTouchMove($event)" @touchend="handleTouchEnd()"
                    {{ $attributes->class(["relative h-64 w-full rounded-box overflow-hidden"]) }}
                >
                    <!-- Slot content -->
                    @foreach($slides as $index => $slide)
                        <div
                            x-cloak
                            x-show="currentSlideIndex == {{ $index + 1 }}"
                            x-transition.opacity.duration.500ms
                            @class(["absolute inset-0", "cursor-pointer" => data_get($slide, 'url') ])
                            @if(data_get($slide, 'url'))
                                @click="window.location = '{{ data_get($slide, 'url') }}'"
                            @endif
                        >
                            <!-- Custom content -->
                            @if($content)
                                <div class="absolute inset-0 z-[1]">
                                     {{ $content($slide) }}
                                </div>
                            <!-- Default content -->
                            @else
                                  <div
                                      @class([
                                          "absolute inset-0 z-[1] flex flex-col items-center justify-end gap-2 px-20 py-12 text-center",
                                           "bg-gradient-to-t from-slate-900/85" => data_get($slide, 'urlText') || data_get($slide, 'title') || data_get($slide, 'description')
                                      ])
                                >
                                    <!-- Title -->
                                    <h3 class="w-full text-2xl lg:text-3xl font-bold text-white">{{ data_get($slide, 'title') }}</h3>

                                    <!-- Description -->
                                    <div class="w-full text-sm text-white mb-5">{{ data_get($slide, 'description') }}</div>

                                    <!-- Button-->
                                    @if(data_get($slide, 'urlText'))
                                        <a href="{{ data_get($slide, 'url') }}" class="btn btn-sm btn-ghost my-3 border-white text-white hover:border-white hover:scale-105">{{ data_get($slide, 'urlText') }}</a>
                                    @endif
                                </div>
                            @endif

                            <!-- Image -->
                            <img class="w-full h-full inset-0 object-cover" src="{{ data_get($slide, 'image') }}" />
                        </div>
                    @endforeach
                </div>
                <!-- indicators -->
                @if(! $withoutIndicators)
                    <div class="absolute rounded-xl bottom-3 md:bottom-5 left-1/2 z-[2] flex -translate-x-1/2 gap-4 md:gap-3 bg-white/75 px-1.5 py-1 md:px-2 dark:bg-slate-900/75" role="group" aria-label="slides" >
                        <template x-for="(slide, index) in slides">
                            <button class="size-2.5 cursor-pointer rounded-full transition hover:scale-125 bg-slate-700 dark:bg-slate-300" @click="currentSlideIndex = index + 1" :class="[currentSlideIndex === index + 1 ? 'bg-slate-700 dark:bg-slate-300' : 'bg-slate-700/50 dark:bg-slate-300/50']"></button>
                        </template>
                    </div>
                @endif
            </div>
        HTML;
    }
}
