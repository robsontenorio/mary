<?php

namespace Mary\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Code extends Component
{
    public string $uuid;

    public function __construct(
        public ?string $id = null,
        public ?string $label = null,
        public ?string $hint = '',
        public string $language = 'javascript',
        public string $theme = 'github_dark',
        public ?string $lightTheme = 'github_light_default',
        public ?string $darkTheme = 'github_dark',
        public string $height = '200px',
        public string $lineHeight = '2',
        public bool $printMargin = false,
    ) {
        $this->uuid = "mary" . md5(serialize($this)) . $id;
    }

    public function modelName(): ?string
    {
        return $this->attributes->whereStartsWith('wire:model')->first();
    }

    public function render(): View|Closure|string
    {
        return <<<'BLADE'
            <div>
                @if($label)
                    <div class="text-xs font-semibold mt-5 mb-3">{{ $label }}</div>
                @endif

                <div {{ $attributes->whereStartsWith('class')->class(["textarea w-full p-0", "textarea-error" => $errors->has($modelName())]) }} >
                    <div
                        wire:ignore
                        x-data="{
                            editor: null,
                            modelValue: @entangle($attributes->wire('model')),
                            init() {
                                ace.require('ace/ext/language_tools');
                                this.editor = ace.edit($refs.editor);

                                // Basic Settings
                                this.editor.setTheme('ace/theme/{{ $theme }}')
                                this.editor.session.setMode('ace/mode/{{ $language }}');
                                this.editor.setShowPrintMargin({{ json_encode($printMargin) }});
                                this.editor.container.style.lineHeight = {{ $lineHeight }};
                                this.editor.renderer.setScrollMargin(10, 10);

                                // More settings
                                this.editor.setOptions({
                                    enableBasicAutocompletion: true,
                                    enableLiveAutocompletion: true,
                                    enableSnippets: true,
                                });

                                // Initial value
                                this.editor.setValue(this.modelValue || '', -1);

                                // Update value on change
                                this.editor.session.on('change', () => {
                                    this.modelValue = this.editor.getValue();
                                });

                                // Watch for model changes
                                this.$watch('modelValue', value => {
                                    if (this.editor.getValue() !== value) {
                                        this.editor.setValue(value || '', -1);
                                    }
                                });

                                // Watch for theme changes
                                window.addEventListener('theme-changed-class', e => {
                                    (e.detail == 'dark')
                                        ? this.editor.setTheme('ace/theme/{{ $darkTheme }}')
                                        : this.editor.setTheme('ace/theme/{{ $lightTheme }}');
                                })
                            }
                        }"
                        x-init="init()"
                        x-on:theme-change-class.window="console.log('foi...')"
                    >
                        <div x-ref="editor" id="{{ $id }}" style="width: 100%; height: {{ $height }}"></div>
                    </div>
                </div>

                @error($modelName())
                    <div class="text-error text-xs mt-3">{{ $message }}</div>
                @enderror

                @if($hint)
                    <div class="text-xs text-base-content/50 mt-2">{{ $hint }}</div>
                @endif
            </div>
        BLADE;
    }
}
