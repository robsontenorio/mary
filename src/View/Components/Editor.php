<?php

namespace Mary\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Editor extends Component
{
    public string $uuid;

    public string $uploadUrl;

    public function __construct(
        public ?string $label = null,
        public ?string $hint = null,
        public ?string $hintClass = 'label-text-alt text-gray-400 ps-1 mt-2',
        public ?string $disk = 'public',
        public ?string $folder = 'editor',
        public ?array $config = [],
        // Validations
        public ?string $errorField = null,
        public ?string $errorClass = 'text-red-500 label-text-alt p-1',
        public ?bool $omitError = false,
        public ?bool $firstErrorOnly = false,
    ) {
        $this->uuid = "mary" . md5(serialize($this));
        $this->uploadUrl = route('mary.upload', absolute: false);
    }

    public function modelName(): ?string
    {
        return $this->attributes->whereStartsWith('wire:model')->first();
    }

    public function errorFieldName(): ?string
    {
        return $this->errorField ?? $this->modelName();
    }

    public function setup(): string
    {
        $setup = array_merge([
            'menubar' => false,
            'automatic_uploads' => true,
            'quickbars_insert_toolbar' => false,
            'branding' => false,
            'relative_urls' => false,
            'remove_script_host' => false,
            'height' => 300,
            'toolbar' => 'undo redo | align bullist numlist | outdent indent | quickimage quicktable',
            'quickbars_selection_toolbar' => 'bold italic underline strikethrough | forecolor backcolor | link blockquote removeformat | blocks',
        ], $this->config);

        $setup['plugins'] = str('advlist autolink lists link image table quickbars ')->append($this->config['plugins'] ?? '');

        return str(json_encode($setup))->trim('{}')->replace("\"", "'")->toString();
    }

    public function render(): View|Closure|string
    {
        return <<<'HTML'
                @php
                    // Wee need this extra step to support models arrays. Ex: wire:model="emails.0"  , wire:model="emails.1"
                    $uuid = $uuid . $modelName()
                @endphp
                <div>
                    <!-- STANDARD LABEL -->
                    @if($label)
                        <label for="{{ $uuid }}" class="pt-0 label label-text font-semibold">
                            <span>
                                {{ $label }}

                                @if($attributes->get('required'))
                                    <span class="text-error">*</span>
                                @endif
                            </span>
                        </label>
                    @endif

                    <!--  EDITOR  -->
                    <div
                        x-data="
                            {
                                value: @entangle($attributes->wire('model')),
                                uploadUrl: '{{ $uploadUrl }}?disk={{ $disk }}&folder={{ $folder }}&_token={{ csrf_token() }}'
                            }"
                        x-init="
                            tinymce.init({
                                {{ $setup() }},
                                target: $refs.tinymce,
                                images_upload_url: uploadUrl,
                                readonly: {{ json_encode($attributes->get('readonly') || $attributes->get('disabled')) }},

                                @if($attributes->get('disabled'))
                                    content_style: 'body { opacity: 50% }',
                                @else
                                    content_style: 'img { max-width: 100%; height: auto; }',
                                @endif

                                setup: function(editor) {
                                    editor.on('keyup', (e) => value = editor.getContent())
                                    editor.on('change', (e) => value = editor.getContent())
                                    editor.on('init', () =>  editor.setContent(value ?? ''))
                                    editor.on('OpenWindow', (e) => tinymce.activeEditor.topLevelWindow = e.dialog)

                                    // Handles a case where people try to change contents on the fly from Livewire methods
                                    $watch('value', function (newValue) {
                                        if (newValue !== editor.getContent()) {
                                            editor.resetContent(newValue || '');
                                        }
                                    })
                                },
                                file_picker_callback: function(cb, value, meta) {
                                    const formData = new FormData()
                                    const input = document.createElement('input');
                                    input.setAttribute('type', 'file');
                                    input.click();

                                    tinymce.activeEditor.topLevelWindow.block('');

                                    input.addEventListener('change', (e) => {
                                        formData.append('file', e.target.files[0])
                                        formData.append('_token', '{{ csrf_token() }}')

                                        fetch(uploadUrl, { method: 'POST', body: formData })
                                           .then(response => response.json())
                                           .then(data => cb(data.location))
                                           .catch((err) => console.error(err))
                                           .finally(() => tinymce.activeEditor.topLevelWindow.unblock());
                                    });
                                }
                            })
                        "
                        x-on:livewire:navigating.window="tinymce.activeEditor.destroy();"
                        wire:ignore
                    >
                        <input id="{{ $uuid }}" x-ref="tinymce" type="textarea" {{ $attributes->whereDoesntStartWith('wire:model') }} />
                    </div>

                    <!-- ERROR -->
                    @if(!$omitError && $errors->has($errorFieldName()))
                        @foreach($errors->get($errorFieldName()) as $message)
                            @foreach(Arr::wrap($message) as $line)
                                <div class="{{ $errorClass }}" x-classes="text-red-500 label-text-alt p-1">{{ $line }}</div>
                                @break($firstErrorOnly)
                            @endforeach
                            @break($firstErrorOnly)
                        @endforeach
                    @endif

                    <!-- HINT -->
                    @if($hint)
                        <div class="{{ $hintClass }}" x-classes="label-text-alt text-gray-400 ps-1 mt-2">{{ $hint }}</div>
                    @endif
                </div>
            HTML;
    }
}
