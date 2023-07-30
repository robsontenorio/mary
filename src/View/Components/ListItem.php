<?php

namespace Mary\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Illuminate\View\Component;

class ListItem extends Component
{
    public string $uuid;

    public function __construct(
        public mixed $item = '',
        public string $avatar = 'avatar',
        public string $key = 'id',
        public string $value = 'name',
        public string $subValue = '',
        public bool $noSeparator = false,
        public string $link = '',
        public mixed $action = '',
    ) {
        $this->uuid = Str::uuid();
    }

    public function render(): View|Closure|string
    {
        return <<<'HTML'
            <div wire:key="{{ $uuid }}">                            
            <div class="grid  grid-flow-col grid-cols-[auto_minmax(auto,1fr)] items-center w-full gap-4">
                    <div >
                        @if($link) <a href="{{ $link }}" wire:navigate> @endif
                            <div class="avatar">
                                <div class="w-12 rounded-full">
                                    <img src="{{ $item->$avatar }}" />
                                </div>
                            </div>
                        @if($link)</a>@endif
                    </div>
                    <div>
                        @if($link) <a href="{{ $link }}" wire:navigate> @endif
                            <div class="font-semibold">
                                {{ $item->$value }}
                            </div>
                            <div class="text-gray-400 text-sm">
                                {{ $item->$subValue }}
                            </div>
                        @if($link)</a>@endif
                    </div>
                    <div>
                        {{ $action }}                        
                    </div>
                </div>
                @if(!$noSeparator) <hr class="my-3" /> @endif
            </div>
        HTML;
    }
}
