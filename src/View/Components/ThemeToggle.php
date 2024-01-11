<?php

namespace Mary\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class ThemeToggle extends Component
{
    public string $uuid;

    public function __construct(
        public ?string $value = null,
        public ?string $light = "Light",
        public ?string $dark = "Dark",
        public ?bool $withLabel = false,

    ) {
        $this->uuid = "mary" . md5(serialize($this));
    }

    public function render(): View|Closure|string
    {
        return <<<'HTML'
                <div>
                    <label
                        x-data="{
                            theme: $persist('light').as('mary-theme'),
                            init() {
                                if (this.theme == 'dark') {
                                    this.$refs.sun.classList.add('swap-off');
                                    this.$refs.sun.classList.remove('swap-on');
                                    this.$refs.moon.classList.add('swap-on');
                                    this.$refs.moon.classList.remove('swap-off');
                                }
                            },
                            toggle() {
                                this.theme = this.theme == 'light' ? 'dark' : 'light'
                                document.documentElement.setAttribute('data-theme', this.theme)
                                document.documentElement.setAttribute('class', this.theme)
                                this.$dispatch('theme-changed', this.theme)
                            }
                        }"
                        @mary-toggle-theme.window="toggle()"
                        {{ $attributes->class("swap swap-rotate") }}
                    >
                        <input type="checkbox" class="theme-controller opacity-0" @click="toggle()" :value="theme" />
                        <x-mary-icon x-ref="sun" name="o-sun" class="swap-on" />
                        <x-mary-icon x-ref="moon" name="o-moon" class="swap-off"  />
                    </label>
                </div>
                <script>
                    document.documentElement.setAttribute("data-theme", localStorage.getItem("mary-theme")?.replaceAll("\"", ""))
                    document.documentElement.setAttribute("class", localStorage.getItem("mary-theme")?.replaceAll("\"", ""))
                </script>
            HTML;
    }
}
