<?php

namespace Mary\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class ImageGallery extends Component
{
    public string $uuid;

    public function __construct(
        public array $images,
        public ?bool $withArrows = false,
        public ?bool $withIndicators = false

    ) {
        $this->uuid = "mary" . md5(serialize($this));
    }

    public function render(): View|Closure|string
    {
        return <<<'HTML'
                <div
                    x-data="{
                        init() {
                            const lightbox = new PhotoSwipeLightbox({
                                gallery: '#gallery-{{ $uuid }}',
                                children: 'a',
                                showHideAnimationType: 'fade',
                                pswpModule: PhotoSwipe
                            });

                            lightbox.init();
                        }
                    }"
                >
                    <div id="gallery-{{ $uuid }}" {{ $attributes->class("pswp-gallery pswp-gallery--single-column carousel") }} >
                        @foreach($images as $image)
                            <a
                                class="carousel-item"
                                href="{{ $image }}"
                                target="_blank"
                                data-pswp-width="200"
                                data-pswp-height="200"
                                x-init="
                                        $el.setAttribute('data-pswp-width', document.getElementById('image-{{ $uuid }}-{{ $loop->index }}').naturalWidth);
                                        $el.setAttribute('data-pswp-height', document.getElementById('image-{{ $uuid }}-{{ $loop->index }}').naturalHeight);
                                    "
                            >
                                <img src="{{ $image }}" class="object-cover" id="image-{{ $uuid }}-{{ $loop->index }}" />
                            </a>
                        @endforeach
                    </div>
                </div>
            HTML;
    }
}
