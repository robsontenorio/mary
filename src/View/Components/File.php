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
        public ?bool $hideErrors = false,
        public ?bool $hideProgress = false,
        public ?bool $changeButton = false,
        public ?bool $cropButton = false,
        public ?bool $revertButton = false,
        public ?bool $cropAfterChange = false,
        public ?string $changeText = "Change",
        public ?string $cropText = "Crop",
        public ?string $revertText = "Revert",
        public ?string $cropTitleText = "Edit image",
        public ?string $cropCancelText = "Cancel",
        public ?string $cropSaveText = "Save",

    ) {
        $this->uuid = "mary" . md5(serialize($this));
    }

    public function modelName(): ?string
    {
        $name = $this->attributes->whereStartsWith('wire:model')->first();

        return $this->attributes->has('multiple') ? "$name.*" : $name;
    }

    public function render(): View|Closure|string
    {
        return <<<'HTML'
                 <div
                    x-data="{
                        progress: 0,
                        cropper: null,
                        fileChanged: false,
                        imageJustCropped: false,
                        uploadFinished: false,
                        cropAfterChange: {{ json_encode($cropAfterChange) }},
                        file: @entangle($attributes->wire('model')),
                        init () {

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

                            $refs.crop.querySelector('img').addEventListener('load', (event) => {
                                if (event.target.complete) {
                                    this.$dispatch('image-reloaded')
                                }
                            })

                            this.$refs.file.click()
                        },
                        cropAfterReload () {
                            if (this.fileChanged && this.uploadFinished && !this.imageJustCropped){
                                this.crop()
                            }
                        },
                        crop() {
                            $refs.maryCrop.showModal()
                            this.cropper?.destroy()
                            let image = $refs.crop.querySelector('img')

                            this.cropper = new Cropper(image, {
                                autoCropArea: 1,
                                viewMode: 1
                            });
                        },
                        revert() {
                             $wire.$removeUpload('{{ $attributes->wire('model')->value }}', this.file.split('livewire-file:').pop())
                        },
                        async save() {
                            $refs.maryCrop.close();
                            this.imageJustCropped = true
                            this.fileChanged = false

                            this.cropper.getCroppedCanvas().toBlob((blob) => {
                                @this.upload('{{ $attributes->wire('model')->value }}', blob,
                                    (uploadedFilename) => {   },
                                    (error) => {  },
                                    (event) => { this.progress = event.detail.progress }
                                )
                            })
                        }
                     }"

                    x-on:livewire-upload-progress="progress = $event.detail.progress; console.log($event.detail.progress)"
                    x-on:livewire-upload-finish="uploadFinished = true; imageJustCropped = false; console.log('finished')"
                    x-on:image-reloaded="cropAfterReload()"
                >
                    <!-- STANDARD LABEL -->
                    @if($label)
                        <label class="pt-0 label label-text font-semibold">{{ $label }}</label>
                    @endif

                    <!-- PROGRESS BAR  -->
                    @if(! $hideProgress)
                        <div class="h-1 -mt-5 mb-5">
                            <progress
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
                        @change="fileChanged = true"

                        {{
                            $attributes->class([
                                "file-input file-input-bordered file-input-primary",
                                "hidden" => $slot->isNotEmpty()
                            ])
                        }}
                    />

                    @if ($slot->isNotEmpty())
                        <!-- PREVIEW AREA -->
                        <div class="relative flex" :class="processing && 'opacity-50'">
                            <div class="cursor-pointer hover:scale-105 transition-all tooltip" data-tip="{{ $changeText }}" @click="change()">
                                {{ $slot }}
                            </div>
                            <div :class="processing && 'loading loading-spinner absolute top-5 left-5'"></div>
                        </div>

                        <!-- BUTTONS -->
                        @if($changeButton || $cropButton || $revertButton)
                            <div class="flex gap-3 my-3">
                                <x-mary-button @click="change()"  icon="o-pencil" :tooltip="$changeText" ::disabled="processing" @class(["btn-sm ", "!hidden" => !$changeButton]) />
                                <x-mary-button @click="crop()" icon="o-scissors"  :tooltip="$cropText" ::disabled="!file || processing"  @class(["btn-sm ", "!hidden" => !$cropButton]) />
                                <x-mary-button @click="revert()" icon="o-arrow-uturn-left" :tooltip="$revertText"  ::disabled="!file || processing" @class(["btn-sm ", "!hidden" => !$revertButton]) />
                            </div>
                        @endif

                        <!-- CROP MODAL -->
                        <div @click.prevent="" x-ref="crop">
                            <!-- TODO: add x-mary-modal -->
                            <x-modal id="maryCrop{{ $uuid }}" x-ref="maryCrop" :title="$cropTitleText" separator class="backdrop-blur-sm" persistent @keydown.window.esc.prevent="">
                                {{ $slot }}
                                <x-slot:actions>
                                    <x-button :label="$cropCancelText" @click="close()" />
                                    <x-button :label="$cropSaveText" class="btn-primary" @click="save()" ::disabled="processing" />
                                </x-slot:actions>
                            </x-modal>
                        </div>
                    @endif

                    <!-- ERROR -->
                    @if (! $hideErrors)
                        @error($modelName())
                            <div class="text-red-500 label-text-alt p-1">{{ $message }}</div>
                        @enderror
                    @endif

                    <!-- HINT -->
                    @if($hint)
                        <div class="label-text-alt text-gray-400 p-1 pb-0">{{ $hint }}</div>
                    @endif
                </div>
            HTML;
    }
}
