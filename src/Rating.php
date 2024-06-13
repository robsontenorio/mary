<?php

namespace Mary\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Rating extends Component
{
    public string $uuid;
    public int $number = 0;
    
    public function __construct(
        public ?string $name = null,
        public int $checked = 0,
        public ?string $color = null,
        public string $size = 'md',
        public ?bool $ratingHidden = false,
        public ?bool $halfStars = false
    ) {
        $this->uuid = "mary" . md5(serialize($this));
        $this->number = $this->halfStars? 10 : 5;
    }

    public function render(): View|Closure|string
    {
        return <<<'HTML'
                <div class="rating rating-{{ $size }} @if($halfStars) rating-half @endif">
                    @if($ratingHidden)
                        <input type="radio" name="{{ $name }}" class="rating-hidden" />
                    @endif
                    @for ($i = 0; $i < $number; $i++)
                        <input 
                            type="radio" 
                            name="{{ $name }}" 
                            class="mask mask-star 
                                @if($halfStars) 
                                    {{ $i % 2 == 0 ? 'mask-half-1' : 'mask-half-2' }} 
                                @endif 
                                @if($color) 
                                    {{ $color }} 
                                @endif" 
                            {{ $i < $checked ? 'checked' : '' }} />
                    @endfor
                </div>
            HTML;
    }
}
