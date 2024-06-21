<?php

namespace Mary\View\Components;

use Closure;
use Illuminate\View\Component;
use Illuminate\View\View;

class Carousel extends Component
{
    public string $uuid;
    public bool $associative = false;

    public function __construct(
        public array $images,
        public ?bool $withArrows = false,
        public ?bool $withIndicators = false,
    ) {
        $this->uuid = "mary" . md5(serialize($this));
        $this->associative =  gettype($this->images[0])=="array"
        ? true
        : false;
    }

    public function render(): View|Closure|string
    {
        return <<<'HTML'
            <div>
                @if($withArrows)
                    <div
                        {{ $attributes->class(['carousel w-full']) }}
                        wire:key="{{ $uuid }}">

                        @for($i=0; $i < count($images); $i++)

                            <div id="slide{{$i}}" class="carousel-item relative w-full justify-center">
                                <a @if($associative) href="{!! $images[$i]['link'] !!}" @endif >
                                    <img src="{!! $associative ? $images[$i]['image']: $images[$i] !!}" class="w-full h-full" />
                                </a>
                                <div class="absolute flex justify-between transform -translate-y-1/2 left-5 right-5 top-1/2">
                                    <a
                                        href="#slide{{($i==0)?$i:$i-1}}"
                                        class="btn btn-circle">❮</a>
                                    <a
                                        href="#slide{{($i==count($images))?$i:$i+1}}"
                                        class="btn btn-circle">❯</a>
                                </div>
                            </div>
                        @endfor
                </div>

                @else
                    <div
                        {{ $attributes->class(['carousel rounded-box']) }}
                        wire:key="{{ $uuid }}">
                        @if($images)
                            @for($i=0; $i < count($images); $i++)
                                <div id='image{!!$i!!}' class="carousel-item">
                                    <a @if($associative) href="{!! $images[$i]['link'] !!}" @endif>
                                        <img  src="{!! $associative ? $images[$i]['image']: $images[$i] !!}"  />
                                    </a>
                                </div>
                            @endfor
                        @endif

                    </div>
                    @if($withIndicators)
                        <div class="flex justify-center w-full py-2 gap-2">
                            @for($i=0; $i < count($images); $i++)
                                <a href='#image{!!$i!!}' class="btn btn-xs">{{$i+1}}</a>
                            @endfor
                        </div>
                    @endif
                @endif
            </div>
        HTML;
    }
}
