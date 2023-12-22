<?php

namespace Mary\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Errors extends Component
{
    public string $uuid;

    public function __construct(
        public ?string $title = null,
        public ?string $description = null,
        public ?string $icon = 'o-x-circle',
        public ?array $only = [],

    ) {
        $this->uuid = "mary" . md5(serialize($this));
    }

    public function render(): View|Closure|string
    {
        return <<<'HTML'
                    <div>
                        @if ($errors->any())
                            <div {{ $attributes->class(['alert alert-error rounded-md']) }} >
                                <x-mary-icon :name="$icon" class="w-8 h-8" />

                                <div>
                                    @if($title)
                                        <div class="font-bold text-lg">{{ $title }}</div>
                                    @endif

                                    @if($description)
                                        <div class="text-sm">{{ $description }}</div>
                                    @endif

                                    <div @class(["mt-5" => $title || $description])>
                                        <ul class="list-disc ml-5">
                                           @foreach ($errors->all() as $error)
                                               <li>{{ $error }}</li>
                                           @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        @endif
                </div>
            HTML;
    }
}
