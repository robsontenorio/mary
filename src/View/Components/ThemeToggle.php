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
                            toggle() {
                                this.theme = $refs.input.checked ? 'dark' : 'light'

                                if (this.theme === 'dark') {
                                    document.documentElement.classList.add('dark')
                                    document.documentElement.classList.remove('light')
                                } else {
                                    document.documentElement.classList.add('light')
                                    document.documentElement.classList.remove('dark')
                                }

                                document.documentElement.setAttribute('data-theme', this.theme)
                                this.$dispatch('theme-changed', this.theme)
                            }
                        }"
                        @mary-toggle-theme.window="toggle()"
                        {{ $attributes->class(["swap swap-rotate", "focus-within:outline focus-within:outline-2 focus-within:outline-offset-2" => Str::contains($attributes->get('class'), "btn")]) }}
                    >
                        <input x-ref="input" type="checkbox" class="theme-controller opacity-0" @click="toggle()" value="dark" />
                        <x-mary-icon x-ref="sun" name="o-sun" class="swap-on" />
                        <x-mary-icon x-ref="moon" name="o-moon" class="swap-off"  />
                    </label>
                </div>
                <script>
                    if (window.maryUiThemeInitialized === undefined) {
                        // Set initial theme from three possible sources
                        savedTheme = localStorage.getItem("mary-theme")?.replaceAll("\"", "");
                        // 1. saved theme from localStorage
                        definedTheme = document.documentElement.getAttribute("data-theme") || document.documentElement.classList.contains("dark") || null;
                        // 2. defined theme from the <html>-tag
                        browserTheme = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? "dark" : "light";
                        // 3. browser theme/color-scheme
                        // If none of the above would trigger and the browser doesn't support color-scheme it defaults to 'light'
                        endTheme = savedTheme ?? definedTheme ?? browserTheme;
                        document.querySelector(".theme-controller").checked = endTheme === "dark"
                        window.localStorage.setItem("mary-theme", JSON.stringify(endTheme))
                        document.documentElement.setAttribute("data-theme", endTheme)
                        
                        if (endTheme === "dark") {
                            document.documentElement.classList.add("dark")
                            document.documentElement.classList.remove("light")
                        } else {
                            document.documentElement.classList.add("light")
                            document.documentElement.classList.remove("dark")
                        }
                        
                        window.maryUiThemeInitialized = true;
                    }
                </script>
            HTML;
    }
}
