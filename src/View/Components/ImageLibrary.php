<?php

namespace Mary\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\View\Component;

class ImageLibrary extends Component
{
    public string $uuid;

    public string $mimes = 'image/png, image/jpeg';

    public function __construct(
        public ?string $label = null,
        public ?string $hint = null,
        public ?bool $hideErrors = false,
        public ?bool $hideProgress = false,
        public ?string $changeText = "Change",
        public ?string $cropText = "Crop",
        public ?string $removeText = "Remove",
        public ?string $cropTitleText = "Crop image",
        public ?string $cropCancelText = "Cancel",
        public ?string $cropSaveText = "Crop",
        public ?string $addFilesText = "Add images",
        public ?array $cropConfig = [],
        public Collection $preview = new Collection(),

    ) {
        $this->uuid = "mary" . md5(serialize($this));
    }

    public function modelName(): ?string
    {
        return $this->attributes->wire('model');
    }

    public function mediaName(): ?string
    {
        return $this->attributes->wire('media');
    }

    public function validationMessage(string $message): string
    {
        return str($message)->after('field');
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
                        files: @entangle($modelName()),

                        init () {
                            this.imagePreview = this.$refs.preview?.querySelector('img')
                            this.imageCrop = this.$refs.crop?.querySelector('img')
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

                            this.$refs.files.click()
                        },
                        refreshImage() {

                        },
                        crop() {
                            $refs.maryCrop.showModal()
                            this.cropper?.destroy()

                            this.cropper = new Cropper(this.imageCrop, {{ $cropSetup() }});
                        },
                        removeMedia(uuid, path){
                            $wire.removeMedia(uuid, '{{ $modelName() }}', '{{ $mediaName() }}', path)
                        },
                        refreshMediaOrder(order){
                            $wire.refreshMediaOrder(order, '{{ $mediaName() }}')
                        },
                        refreshMediaSources(){
                            this.processing = 99
                            $wire.refreshMediaSources('{{ $modelName() }}', '{{ $mediaName() }}').then(x => this.progress = 100)
                        },
                        async save() {
                            $refs.maryCrop.close();

                            this.progress = 1
                            this.justCropped = true

                            this.imagePreview.src = this.cropper.getCroppedCanvas().toDataURL()
                            this.imageCrop.src = this.imagePreview.src
                            this.file.files[0] = this.cropper.getCroppedCanvas().toDataURL()

                            return

                            this.cropper.getCroppedCanvas().toBlob((blob) => {
                                @this.upload('{{ $modelName() }}', blob,
                                    (uploadedFilename) => {  },
                                    (error) => {  },
                                    (event) => { this.progress = event.detail.progress }
                                )
                            })
                        }
                     }"

                    x-on:livewire-upload-progress="progress = $event.detail.progress;"
                    x-on:livewire-upload-finish="refreshMediaSources()"


                    {{ $attributes->whereStartsWith('class') }}
                >
                    <!-- STANDARD LABEL -->
                    @if($label)
                        <label class="pt-0 label label-text font-semibold">{{ $label }}</label>
                    @endif

                    <!-- PREVIEW AREA -->
                    <div
                        :class="processing && 'opacity-50 pointer-events-none'"
                        @class(["relative", "hidden" => count($preview) == 0])
                    >
                        <div
                            x-data="{ sortable: null }"
                            x-init="sortable = new Sortable($el, { animation: 150, ghostClass: 'bg-base-300', onEnd: (ev) => refreshMediaOrder(sortable.toArray()) })"
                            class="border border-dotted border-primary rounded-lg"
                        >
                            @foreach($preview as $key => $image)
                                <div class="relative border-b-primary border-b border-dotted last:border-none cursor-move hover:bg-base-200/50" data-id="{{ $image['uuid'] }}">
                                    <div wire:key="preview-{{ $image['uuid'] }}" class="py-2 pl-16 pr-10">
                                        <!-- IMAGE -->
                                        <img src="{{ $image['path'] }}" class="h-24 cursor-pointer border-2 rounded-lg hover:scale-105 transition-all" @click="document.getElementById('file-{{ $uuid}}-{{ $key }}').click()" />

                                        <!-- VALIDATION -->
                                         @error($modelName().'.'.$key)
                                            <div class="text-red-500 label-text-alt p-1">{{ $validationMessage($message) }}</div>
                                         @enderror

                                        <!-- HIDDEN FILE INPUT -->
                                        <input
                                            type="file"
                                            id="file-{{ $uuid}}-{{ $key }}"
                                            wire:model="{{ $modelName().'.'.$key  }}"
                                            accept="{{ $attributes->get('accept') ?? $mimes }}"
                                            class="hidden"
                                            />
                                    </div>

                                    <!-- ACTIONS -->
                                    <div class="absolute flex flex-col gap-2 top-3 left-3 cursor-pointer  p-2 rounded-lg">
                                        <x-mary-button @click="removeMedia('{{ $image['uuid'] }}', '{{ $image['path'] }}')"  icon="o-x-circle" :tooltip="$removeText" ::disabled="processing" @class(["btn-sm btn-ghost btn-circle  "]) />
                                        <x-mary-button @click="crop()" icon="o-scissors"  :tooltip="$cropText" ::disabled="!files || processing"  @class(["btn-sm btn-ghost btn-circle "]) />
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- PROGRESS -->
                        <div
                            x-cloak
                            :style="`--value:${progress}; --size:1.5rem; --thickness: 4px;`"
                            :class="!processing && 'hidden'"
                            class="radial-progress text-success absolute top-5 left-5 bg-neutral"
                            role="progressbar"></div>
                    </div>

                    <!-- CROP MODAL -->
                    <div @click.prevent="" x-ref="crop" wire:ignore>
                            <x-mary-modal id="maryCrop{{ $uuid }}" x-ref="maryCrop" :title="$cropTitleText" separator class="backdrop-blur-sm" persistent @keydown.window.esc.prevent="">
                                <img src="#" />
                                <x-slot:actions>
                                    <x-button :label="$cropCancelText" @click="close()" />
                                    <x-button :label="$cropSaveText" class="btn-primary" @click="save()" ::disabled="processing" />
                                </x-slot:actions>
                            </x-mary-modal>
                        </div>

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

                    <!-- ADD FILES -->
                    <div @click="$refs.files.click()" class="btn btn-block mt-3 ">
                        <x-mary-icon name="o-plus-circle" label="{{ $addFilesText }}" />
                    </div>

                    <!-- MAIN FILE INPUT -->
                    <input
                        id="{{ $uuid }}"
                        type="file"
                        x-ref="files"
                        class="file-input file-input-bordered file-input-primary hidden"
                        wire:model="{{ $modelName() }}.*"
                        accept="{{ $attributes->get('accept') ?? $mimes }}"
                        multiple />

                    <!-- ERROR -->
                    @if (! $hideErrors)
                        @error($mediaName())
                            <div class="text-red-500 label-text-alt p-1 pt-2">{{ $message }}</div>
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
