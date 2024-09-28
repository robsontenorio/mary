<?php

namespace Mary\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Illuminate\View\Component;

class MenuItem extends Component
{
    public string $uuid;

    public function __construct(
        public ?string $title = null,
        public ?string $icon = null,
        public ?string $spinner = null,
        public ?string $link = null,
        public ?string $route = null,
        public ?bool $external = false,
        public ?bool $noWireNavigate = false,
        public ?string $badge = null,
        public ?string $badgeClasses = null,
        public ?bool $active = false,
        public ?bool $separator = false,
        public ?bool $enabled = true,
        public ?bool $exact = false
    ) {
        $this->uuid = "mary" . md5(serialize($this));
    }

    public function spinnerTarget(): ?string
    {
        if ($this->spinner == 1) {
            return $this->attributes->whereStartsWith('wire:click')->first();
        }

        return $this->spinner;
    }

    public function routeMatches(): bool
    {
        if ($this->link == null) {
            return false;
        }

        if ($this->route) {
            return request()->routeIs($this->route);
        }

        $link = url($this->link ?? '');
        $route = url(request()->url());

        if ($link == $route) {
            return true;
        }

        return ! $this->exact && $this->link != '/' && Str::startsWith($route, $link);
    }

    public function render(): View|Closure|string
    {
        if ($this->enabled === false) {
            return '';
        }

        return <<<'HTML'
                @aware(['activateByRoute' => false, 'activeBgColor' => 'bg-base-300'])

                <li>
                    <a
                        {{
                            $attributes->class([
                                "my-0.5 hover:text-inherit rounded-md whitespace-nowrap ",
                                "mary-active-menu $activeBgColor" => ($active || ($activateByRoute && $routeMatches()))
                            ])
                        }}

                        @if($link)
                            href="{{ $link }}"

                            @if($external)
                                target="_blank"
                            @endif

                            @if(!$external && !$noWireNavigate)
                                wire:navigate
                            @endif
                        @endif

                        @if($spinner)
                            wire:target="{{ $spinnerTarget() }}"
                            wire:loading.attr="disabled"
                        @endif
                    >
                        <!-- SPINNER -->
                        @if($spinner)
                            <span wire:loading wire:target="{{ $spinnerTarget() }}" class="loading loading-spinner w-5 h-5"></span>
                        @endif

                        @if($icon)
                            <span class="block -mt-0.5" @if($spinner) wire:loading.class="hidden" wire:target="{{ $spinnerTarget() }}" @endif>
                                <x-mary-icon :name="$icon" />
                            </span>
                        @endif

                        @if($title || $slot->isNotEmpty())
                        <span class="mary-hideable whitespace-nowrap truncate">
                            @if($title)
                                {{ $title }}

                                @if($badge)
                                    <span class="badge badge-ghost badge-sm {{ $badgeClasses }}">{{ $badge }}</span>
                                @endif
                            @else
                                {{ $slot }}
                            @endif
                        </span>
                        @endif
                    </a>
                </li>
            HTML;
    }
}
