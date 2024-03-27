<?php

namespace Mary\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Markdown extends Component
{
    public string $uuid;

    public function __construct(
        public ?string $label = null,
        public ?string $disk = 'public',
        public ?string $folder = 'markdown',
        public ?array $config = [],
        // Validations
        public ?string $errorField = null,
        public ?string $errorClass = 'text-red-500 label-text-alt p-1',
        public ?bool $omitError = false,
        public ?bool $firstErrorOnly = false,
    ) {
        $this->uuid = "mary" . md5(serialize($this));
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
            'spellChecker' => false,
            'autoSave' => false,
            'uploadImage' => true,
            'imageAccept' => 'image/png, image/jpeg, image/gif, image/avif',
            'toolbar' => [
                'heading', 'bold', 'italic', 'strikethrough', '|',
                'code', 'quote', 'unordered-list', 'ordered-list', 'horizontal-rule', '|',
                'link', 'upload-image', 'table', '|',
                'preview', 'side-by-side'
            ],
        ], $this->config);

        // Table default CSS class `.table` breaks the layout.
        // Here is a workaround
        $table = "{ 'title' : 'Table', 'name' : 'myTable', 'action' : EasyMDE.drawTable, 'className' : 'fa fa-table' }";

        return str(json_encode($setup))
            ->replace("\"", "'")
            ->trim('{}')
            ->replace("'table'", $table)
            ->toString();
    }

    public function render(): View|Closure|string
    {
        return <<<'HTML'
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

                <!-- EDITOR -->
                <div
                    x-data="
                        {
                            editor: null,
                            value: @entangle($attributes->wire('model')),
                            uploadUrl: '/mary/upload?disk={{ $disk }}&folder={{ $folder }}&_token={{ csrf_token() }}',
                            uploading: false,
                            init() {
                                this.editor = new EasyMDE({
                                        {{ $setup() }},
                                        element: $refs.markdown{{ $uuid }},
                                        initialValue: this.value ?? '',
                                        imageUploadFunction: (file, onSuccess, onError) => {
                                            if (file.type.split('/')[0] !== 'image') {
                                                return onError('File must be an image.');
                                            }

                                            var data = new FormData()
                                            data.append('file', file)

                                            this.uploading = true

                                            fetch(this.uploadUrl, { method: 'POST', body: data })
                                               .then(response => response.json())
                                               .then(data => onSuccess(data.location))
                                               .catch((err) => onError('Error uploading image!'))
                                               .finally(() => this.uploading = false)
                                        }
                                    })

                                this.editor.codemirror.on('change', () => this.value = this.editor.value())
                            }
                        }"
                    wire:ignore
                    x-on:livewire:navigating.window="editor.toTextArea(); editor = null"
                >
                    <div class="relative disabled" :class="uploading && 'pointer-events-none opacity-50'">
                        <textarea x-ref="markdown{{ $uuid }}"></textarea>

                        <div class="absolute top-1/2 left-1/2 !opacity-100 text-center hidden" :class="uploading && '!block'">
                            <div>Uploading</div>
                            <div class="loading loading-dots"></div>
                        </div>
                    </div>

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
                
            </div>
            HTML;
    }
}
