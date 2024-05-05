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
        public bool $titleImage = false,
        public ?bool $withArrows = false,
        public ?bool $withIndicators = false,
        public ?array $captions = [], // Array containing captions for each image
        public ?array $translations = [], // Translating interface : < > X Zoom
        public ?string $indexIndicatorSep = ' / ', // Separator for slide count indicators
        public ?int $loopImgs = 1, // Whether to enable infinite looping of slides
        public ?float $bgOpacity = 0.8, // Opacity of the background backdrop
        public ?float $spacing = 0.1, // Spacing between slides as a ratio of viewport width
        public ?int $allowPanToNext = 1, // Whether to allow swiping to the next slide when the current slide is zoomed
        public ?int $wheelToZoom = 1, // Whether to enable zooming via mouse wheel (true) or control key + mouse wheel (false)
        public ?int $pinchToClose = 1, // Whether to enable pinch-to-close gesture
        public ?int $closeOnVerticalDrag = 1, // Whether to enable closing the gallery via vertical drag
        public ?array $paddingSlide = ['top' => 0, 'bottom' => 0, 'left' => 0, 'right' => 0], // Padding around the slide area in pixels
        public ?string $showHideAnimationType= 'fade', // Show animation : fade, zoom, none
        public ?int $hideAnimationDuration = 333, // Duration of hide animation in milliseconds
        public ?int $showAnimationDuration = 333, // Duration of show animation in milliseconds
        public ?int $zoomAnimationDuration = 333, // Duration of zoom animation in milliseconds
        public ?int $maxWidthToAnimate = 4000, // Maximum image width for animated transitions
        public ?int $escKey = 1, // Whether to enable closing the gallery with the Esc key
        public ?int $arrowKeys = 1, // Whether to enable navigation with arrow keys
        public ?int $trapFocus = 1, // Whether to trap focus within the gallery when it's open
        public ?int $returnFocus = 1, // Whether to return focus to the previously focused element when the gallery is closed
        public ?int $clickToCloseNonZoomable = 1, // Whether to enable closing the gallery by clicking on a non-zoomable image
        public ?int $preloaderDelay = 2000, // Delay before showing the preloader in milliseconds
        public ?int $preloadFirstSlide = 1, // Whether to preload the first slide
        public ?string $preload = '[1, 2]', // Number of images to preload before and after the current image
        /* Actions of PhotoSwipe : https://photoswipe.com/click-and-tap-actions/ */
        public ?string $imageClickAction = 'zoom-or-close', // Action to perform when clicking on an image
        public ?string $bgClickAction = 'close', // Action to perform when clicking on the background
        public ?string $tapAction = 'zoom-or-close', // Action to perform when tapping on an image
        public ?string $doubleTapAction = 'zoom', // Action to perform when double-tapping on an image
        public ?string $initialZoomLevel = 'fit', // Initial zoom level for images (fill, fit or positive number)
        public ?string $secondaryZoomLevel = null, // Secondary zoom level for images
        public ?string $maxZoomLevel = '4', // Maximum zoom level for images
        public ?string $mainClass = null, // Custom CSS class for the gallery
        public ?string $appendToEl = 'document.body', // Element to which the gallery should be appended
    ) {
        $this->uuid = "mary" . md5(serialize($this));
    }

    public function render(): View|Closure|string
    {
        return <<<'HTML'
                <div
                    x-data="{
                        init() {
                            const options = {
                                gallery: '#gallery-{{ $uuid }}',
                                children: 'a',
                                pswpModule: PhotoSwipe,
                                showHideAnimationType: '{{ $showHideAnimationType }}',
                                bgOpacity: {{ $bgOpacity }},
                                loop: {{ $loopImgs }},
                                spacing: {{ $spacing }},
                                allowPanToNext: {{ $allowPanToNext }},
                                wheelToZoom: {{ $wheelToZoom }},
                                pinchToClose: {{ $pinchToClose }},
                                closeOnVerticalDrag: {{ $closeOnVerticalDrag }},
                                padding: {
                                    top: {{ $paddingSlide['top'] }},
                                    bottom: {{ $paddingSlide['bottom'] }},
                                    left: {{ $paddingSlide['left'] }},
                                    right: {{ $paddingSlide['right'] }}
                                },
                                hideAnimationDuration: {{ $hideAnimationDuration }},
                                showAnimationDuration: {{ $showAnimationDuration }},
                                zoomAnimationDuration: {{ $zoomAnimationDuration }},
                                maxWidthToAnimate: '{{ $maxWidthToAnimate }}',
                                escKey: {{ $escKey }},
                                arrowKeys: {{ $arrowKeys }},
                                trapFocus: {{ $trapFocus }},
                                returnFocus: {{ $returnFocus }},
                                clickToCloseNonZoomable: {{ $clickToCloseNonZoomable }},
                                preloaderDelay: {{ $preloaderDelay }},
                                preloadFirstSlide: {{ $preloadFirstSlide }},
                                preload: {{ $preload }},
                                closeTitle: '{{ $translations['closeTitle'] }}',
                                zoomTitle: '{{ $translations['zoomTitle'] }}',
                                arrowPrevTitle: '{{ $translations['arrowPrevTitle'] }}',
                                arrowNextTitle: '{{ $translations['arrowNextTitle'] }}',
                                errorMsg: '{{ $translations['errorMsg'] }}',
                                indexIndicatorSep: '{{ $indexIndicatorSep }}',
                                imageClickAction: '{{ $imageClickAction }}',
                                bgClickAction: '{{ $bgClickAction }}',
                                tapAction: '{{ $tapAction }}',
                                doubleTapAction: '{{ $doubleTapAction }}',
                                initialZoomLevel: '{{ $initialZoomLevel}}',
                                secondaryZoomLevel: '{{ $secondaryZoomLevel }}',
                                maxZoomLevel: '{{ $maxZoomLevel }}',
                                mainClass: '{{ $mainClass }}',
                                appendToEl: {{ $appendToEl }},
                            };

                            const lightbox = new PhotoSwipeLightbox(options);

                            @if (!empty($captions))
                                lightbox.on('uiRegister', function() {
                                    lightbox.pswp.ui.registerElement({
                                        name: 'custom-caption',
                                        appendTo: 'root',
                                        onInit: (el, pswp) => {
                                            lightbox.pswp.on('change', () => {
                                                const currSlideElement = lightbox.pswp.currSlide.data.element;
                                                let captionHTML = '';
                                                if (currSlideElement) {
                                                    captionHTML = currSlideElement.querySelector('img').getAttribute('alt');
                                                }
                                                el.innerHTML = captionHTML || '';
                                            });
                                        }
                                    });
                                });
                            @endif
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
                                data-pswp-height="200">
                                <img
                                    src="{{ $image }}"
                                    @if (!empty($captions) && array_key_exists($loop->iteration-1, $captions))
                                        @if ($titleImage)
                                            title="{{ $captions[$loop->iteration-1] }}"
                                        @endif
                                        alt="{{ $captions[$loop->iteration-1] }}"
                                    @endif
                                    class="object-cover hover:opacity-70"
                                    onload="this.parentNode.setAttribute('data-pswp-width', this.naturalWidth); this.parentNode.setAttribute('data-pswp-height', this.naturalHeight)"
                                />
                            </a>
                        @endforeach
                    </div>
                </div>
            HTML;
    }
}
