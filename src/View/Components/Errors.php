<?php

namespace Mary\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Errors extends Component
{
    public string $uuid;

    public function __construct(
        public ?string $id = null,
        public ?string $title = null,
        public ?string $description = null,
        public ?string $icon = 'o-x-circle',
        public ?array $only = [],
    ) {
        $this->uuid = "mary" . md5(serialize($this)) . $id;
    }

    public function render(): View|Closure|string
    {
        return <<<'BLADE'
                <div>
                    @if ($errors->any())
                        <div {{ $attributes->class(["alert alert-error rounded rounded-sm"]) }} >
                            <div class="grid gap-3">
                                <div class="flex gap-2">
                                    @if($title)
                                        <x-mary-icon :name="$icon" class="w-6 h-6 mt-0.5" />
                                    @endif
                                    <div>
                                        @if($title)
                                            <div class="font-bold text-lg">{{ $title }}</div>
                                        @endif

                                        @if($description)
                                            <div class="font-semibold">{{ $description }}</div>
                                        @endif
                                    </div>
                                </div>
                                <div>
                                    <ul class="list-disc ms-5 space-y-2 sm:ms-12 pb-3">
                                       @foreach ($errors->all() as $error)
                                           <li>{{ $error }}</li>
                                       @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    @endif
            </div>
            BLADE;
    }
}
