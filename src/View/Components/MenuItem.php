<?php

namespace Mary\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Illuminate\View\Component;

class MenuItem extends Component
{
    public string $uuid;

    public function __construct(
        public ?string $title = null,
        public ?string $icon = null,
        public ?string $link = null,
        public ?string $route = null,
        public ?bool $external = false,
        public ?bool $noWireNavigate = false,
        public ?string $badge = null,
        public ?string $badgeClasses = null,
        public ?bool $active = false,
        public ?bool $separator = false
    ) {
        $this->uuid = "mary" . md5(serialize($this));
    }

    public function routeMatches(): bool
    {
        if ($this->link != null)
        {
            return $this->_linkMatches();
        } elseif ($this->route != null)
        {
            return $this->_routeMatches();
        } else {
            return false;
        }
    }

    private function _routeMatches(): bool
    {
        $routeName = $this->route;
        $currentRouteName = Route::currentRouteName();

        if ($routeName == $currentRouteName) {
            return true;
        }

        return false;
    }

    private function _linkMatches(): bool
    {
        $link = url($this->link ?? '');
        $route = url(request()->url());

        if ($link == $route) {
            return true;
        }

        return $this->link != '/' && Str::startsWith($route, $link);        
    }

    public function render(): View|Closure|string
    {
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

                        @if($link || $route)
                            @if ($link)
                            href="{{ $link }}"
                            @elseif ($route)
                            href="{{ route($route) }}"
                            @endif

                            @if($external)
                                target="_blank"
                            @endif

                            @if(!$external && !$noWireNavigate)
                                wire:navigate
                            @endif
                        @endif
                    >
                        @if($icon)
                            <x-mary-icon :name="$icon" />
                        @endif

                        <span class="mary-hideable whitespace-nowrap">
                            @if($title)
                                {{ $title }}

                                @if($badge)
                                    <span class="badge badge-ghost badge-sm {{ $badgeClasses }}">{{ $badge }}</span>
                                @endif
                            @else
                                {{ $slot }}
                            @endif
                        </span>
                    </a>
                </li>
            HTML;
    }
}
