<?php

namespace Mary\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class File extends Component
{
    public string $uuid;

    public function __construct(
        public ?string $label = null,
        public ?string $hint = null,
        public ?bool $hideProgress = false,
        public ?bool $cropAfterChange = false,
        public ?string $changeText = "Change",
        public ?string $cropTitleText = "Crop image",
        public ?string $cropCancelText = "Cancel",
        public ?string $cropSaveText = "Crop",
        public ?array $cropConfig = [],
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

    public function cropSetup(): string
    {
        return json_encode(array_merge([
            'autoCropArea' => 1,
            'viewMode' => 1,
            'dragMode' => 'move'
        ], $this->cropConfig));
    }

    public function render(): View|Closure|string
    {
        return <<<'HTML'
                 <div
                    x-data="{
                        progress: 0,
                        cropper: null,
                        justCropped: false,
                        fileChanged: false,
                        imagePreview: null,
                        imageCrop: null,
                        originalImageUrl: null,
                        cropAfterChange: {{ json_encode($cropAfterChange) }},
                        file: @entangle($attributes->wire('model')),
                        init () {
                            this.imagePreview = this.$refs.preview?.querySelector('img')
                            this.imageCrop = this.$refs.crop?.querySelector('img')
                            this.originalImageUrl = this.imagePreview?.src

                            this.$watch('progress', value => {
                                if (value == 100 && this.cropAfterChange && !this.justCropped) {
                                    this.crop()
                                }
                            })
                        },
                        get processing () {
                            return this.progress > 0 && this.progress < 100
                        },
                        close() {
                            $refs.maryCrop.close()
                            this.cropper?.destroy()
                        },
                        change() {
                            if (this.processing) {
                                return
                            }

                            this.$refs.file.click()
                        },
                        refreshImage() {
                            this.progress = 1
                            this.justCropped = false

                            if (this.imagePreview?.src) {
                                this.imagePreview.src = URL.createObjectURL(this.$refs.file.files[0])
                                this.imageCrop.src = this.imagePreview.src
                            }
                        },
                        crop() {
                            $refs.maryCrop.showModal()
                            this.cropper?.destroy()

                            this.cropper = new Cropper(this.imageCrop, {{ $cropSetup() }});
                        },
                        revert() {
                             $wire.$removeUpload('{{ $attributes->wire('model')->value }}', this.file.split('livewire-file:').pop(), () => {
                                this.imagePreview.src = this.originalImageUrl
                             })
                        },
                        async save() {
                            $refs.maryCrop.close();

                            this.progress = 1
                            this.justCropped = true

                            this.imagePreview.src = this.cropper.getCroppedCanvas().toDataURL()
                            this.imageCrop.src = this.imagePreview.src

                            this.cropper.getCroppedCanvas().toBlob((blob) => {
                                @this.upload('{{ $attributes->wire('model')->value }}', blob,
                                    (uploadedFilename) => {  },
                                    (error) => {  },
                                    (event) => { this.progress = event.detail.progress }
                                )
                            })
                        }
                     }"

                    x-on:livewire-upload-progress="progress = $event.detail.progress;"

                    {{ $attributes->whereStartsWith('class') }}
                >
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

                    <!-- PROGRESS BAR  -->
                    @if(! $hideProgress && $slot->isEmpty())
                        <div class="h-1 -mt-5 mb-5">
                            <progress
                                x-cloak
                                :class="!processing && 'hidden'"
                                :value="progress"
                                max="100"
                                class="progress progress-success h-1 w-56"></progress>
                        </div>
                    @endif

                    <!-- FILE INPUT -->
                    <input
                        id="{{ $uuid }}"
                        type="file"
                        x-ref="file"
                        @change="refreshImage()"

                        {{
                            $attributes->whereDoesntStartWith('class')->class([
                                "file-input file-input-bordered file-input-primary",
                                "hidden" => $slot->isNotEmpty()
                            ])
                        }}
                    />

                    @if ($slot->isNotEmpty())
                        <!-- PREVIEW AREA -->
                        <div x-ref="preview" class="relative flex">
                            <div
                                wire:ignore
                                @click="change()"
                                :class="processing && 'opacity-50 pointer-events-none'"
                                class="cursor-pointer hover:scale-105 transition-all tooltip"
                                data-tip="{{ $changeText }}"
                            >
                                {{ $slot }}
                            </div>
                            <!-- PROGRESS -->
                            <div
                                x-cloak
                                :style="`--value:${progress}; --size:1.5rem; --thickness: 4px;`"
                                :class="!processing && 'hidden'"
                                class="radial-progress text-success absolute top-5 left-5 bg-neutral"
                                role="progressbar"
                            ></div>
                        </div>

                        <!-- CROP MODAL -->
                        <div @click.prevent="" x-ref="crop" wire:ignore>
                            <x-mary-modal id="maryCrop{{ $uuid }}" x-ref="maryCrop" :title="$cropTitleText" separator class="backdrop-blur-sm" persistent @keydown.window.esc.prevent="">
                                <img src="#" />
                                <x-slot:actions>
                                    <x-mary-button :label="$cropCancelText" @click="close()" />
                                    <x-mary-button :label="$cropSaveText" class="btn-primary" @click="save()" ::disabled="processing" />
                                </x-slot:actions>
                            </x-mary-modal>
                        </div>
                    @endif

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

                    <!-- MULTIPLE -->
                    @error($modelName().'.*')
                        <div class="text-red-500 label-text-alt p-1 pt-2">{{ $message }}</div>
                    @enderror

                    <!-- HINT -->
                    @if($hint)
                        <div class="label-text-alt text-gray-400 p-1 pb-0">{{ $hint }}</div>
                    @endif
                </div>
            HTML;
    }
}
