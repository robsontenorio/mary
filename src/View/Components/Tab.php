<?php

namespace Mary\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Blade;
use Illuminate\View\Component;

class Tab extends Component
{
    public string $uuid;

    public function __construct(
        public ?string $name = null,
        public ?string $label = null,
        public ?string $icon = null
    ) {
        $this->uuid = "mary" . md5(serialize($this));
    }

    public function tabLabel(string $label): string
    {
        $fromLabel = $this->label ? $this->label : $label;

        if ($this->icon) {
            return Blade::render("
                <x-mary-icon name='" . $this->icon . "' class='me-2 whitespace-nowrap'>
                    <x-slot:label>
                        {$fromLabel}
                    </x-slot:label>
                </x-mary-icon>
            ");
        }

        return Blade::render("
            <div class='whitespace-nowrap'>
                {$fromLabel}
            </div>
        ");
    }

    public function render(): View|Closure|string
    {
        return <<<'HTML'
                    <a
                        class="hidden"
                        :class="{ 'tab-active': selected === '{{ $name }}' }"
                        data-name="{{ $name }}"
                        x-init="
                                const newItem = { name: '{{ $name }}', label: {{ json_encode($tabLabel($label)) }} };
                                const index = tabs.findIndex(item => item.name === '{{ $name }}');
                                index !== -1 ? tabs[index] = newItem : tabs.push(newItem);

                                Livewire.hook('morph.removed', ({el}) => {
                                    if (el.getAttribute('data-name') == '{{ $name }}'){
                                        tabs = tabs.filter(i => i.name !== '{{ $name }}')
                                    }
                                })
                            "
                    ></a>

                    <div x-show="selected === '{{ $name }}'" role="tabpanel" {{ $attributes->class("tab-content py-5 px-1") }}>
                        {{ $slot }}
                    </div>
                HTML;
    }
}
