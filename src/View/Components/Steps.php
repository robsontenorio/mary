<?php

namespace Mary\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Steps extends Component
{
    public string $uuid;

    public function __construct(
        public bool $vertical = false

    ) {
        $this->uuid = "mary" . md5(serialize($this));
    }

    public function render(): View|Closure|string
    {
        return <<<'HTML'
                <div
                        x-data="{
                                steps: [],
                                current: @entangle($attributes->wire('model')),
                                init() {
                                    // Fix weird issue when navigating back
                                    document.addEventListener('livewire:navigating', () => {
                                        document.querySelectorAll('.step').forEach(el =>  el.remove());
                                    });
                                }
                        }"
                    >
                        <!-- STEP LABELS -->
                        <ul class="steps">
                            <template x-for="(step, index) in steps" :key="index">
                                <li x-html="step.text" class="step" :class="(index + 1 <= current) && 'step-primary'"></li>
                            </template>
                        </ul>

                        <!-- STEP PANELS-->
                        <div {{ $attributes->whereDoesntStartWith('wire') }}>
                            {{ $slot }}
                        </div>
                    </div>
            HTML;
    }
}
