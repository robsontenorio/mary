<?php

namespace Mary\View\Components;

use Illuminate\View\Component;

class TableColumnsSelector extends Component
{
    public string $uuid;

    public string $headerUuid;

    public function __construct(
        public array $headers,
        public ?string $label = null,
        public ?string $icon = 's-view-columns',
        public ?bool $right = false,
        public ?bool $top = false,
        public ?bool $noXAnchor = false,
    ) {
        $this->uuid = 'mary'.md5(serialize($this));
        $this->headerUuid = 'mary'.md5(json_encode($this->headers));
    }

    public function render(): string
    {
        return <<<'HTML'
        <div>
            <details
                x-data="{
                    open: false,
                    columns: {},
                    headerUuid: '{{ $headerUuid }}',
                    loadSettings() {
                        const savedSettings = localStorage.getItem(this.headerUuid);
                        if (savedSettings) {
                            this.columns = JSON.parse(savedSettings);
                        } else {
                            // Initialize with all columns visible if no settings found
                            @foreach($headers as $header)
                                this.columns['{{ $header['key'] }}'] = true;
                            @endforeach
                        }
                    },
                    saveSettings() {
                        localStorage.setItem(this.headerUuid, JSON.stringify(this.columns));
                    }
                }"
                x-init="loadSettings()"
                @click.outside="open = false"
                :open="open"
                class="dropdown dropdown-bottom"
            >
                <summary x-ref="button" @click.prevent="open = !open" {{ $attributes->class(['btn normal-case']) }}>
                    {{ $label }}
                    <x-mary-icon :name="$icon" />
                </summary>

                <ul
                    @class([
                        'p-2','shadow','menu','z-[1]','border','border-base-200','bg-base-100','dark:bg-base-200','rounded-box','w-auto','min-w-max',
                        'dropdown-content' => $noXAnchor,
                    ])
                    @if(!$noXAnchor)
                        x-anchor.{{ $right ? 'bottom-end' : 'bottom-start' }}="$refs.button"
                    @endif
                >
                    <div class="grid gap-y-4 p-6">
                        <h4 class="text-base font-semibold leading-6 text-gray-950 dark:text-white">Columns</h4>
                        <div style="--cols-default: repeat(1, minmax(0, 1fr)); --cols-lg: repeat(1, minmax(0, 1fr));"
                             class="grid grid-cols-[--cols-default] lg:grid-cols-[--cols-lg] gap-6">
                            @foreach($headers as $header)
                            <div style="--col-span-default: span 1 / span 1;" class="col-[--col-span-default]">
                                <div class="grid gap-y-2">
                                    <div class="flex gap-3">
                                        <label class="cursor-pointer inline-flex gap-x-2">
                                         <div class="swap swap-flip ">
                                            <input type="checkbox"
                                                   x-model="columns['{{ $header['key'] }}']"
                                                   @change="saveSettings(); $dispatch('column-visibility-changed', { key: '{{ $header['key'] }}', visible: columns['{{ $header['key'] }}'] })"
                                                   >
                                            <x-mary-icon name="o-eye" class="swap-on fill-current" />
                                            <x-mary-icon name="o-eye-slash" class="swap-off fill-current" />
                                            </div>
                                             <span class="text-sm">
                                                {{ data_get($header, 'label') }}
                                            </span>
                                        </label>
                                        
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </ul>
            </details>
        </div>
        HTML;
    }
}

