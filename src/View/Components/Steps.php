<?php
namespace Mary\View\Components;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
class Steps extends Component
{
    public string $uuid;
    public function __construct(
        public bool $vertical = false,
        public ?string $stepsColor = 'step-neutral',
        public ?string $stepperClasses = null
    ) {
        $this->uuid = "mary" . md5(serialize($this));
    }
    public function render(): View|Closure|string
    {
        return <<<'BLADE'
                <div x-data="{ steps: [], current: @entangle($attributes->wire('model')),  init() { document.addEventListener('livewire:navigating', () => { document.querySelectorAll('.step').forEach(el =>  el.remove()); }); } }" >
                    <ul class="steps [&>*:nth-child(2)]:before:hidden {{ $stepperClasses }}">
                        <template x-for="(step, index) in steps" :key="index">
                            <li class="step" :data-content="!step.icon ? step.dataContent || (index + 1) : ''" :class="(index + 1 <= current) && '{{ $stepsColor }} ' + step.classes" >
                                <template x-if="step.icon"> <span x-html="step.icon" class="step-icon"></span> </template> <span x-html="step.text"></span>
                            </li>
                        </template>
                    </ul>
                    <div {{ $attributes->whereDoesntStartWith('wire') }}> {{ $slot }} </div> <span class="hidden step-primary step-error step-success step-neutral step-info step-accent"></span>
                </div>
            BLADE;
    }
}
