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
    ) {
        $this->uuid = md5(serialize($this));
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
                    x-data="{ progress: 0 }"
                    x-on:livewire-upload-progress="progress = $event.detail.progress;"
                >
                    <!-- STANDARD LABEL -->
                    @if($label)
                        <label class="pt-0 label label-text font-semibold">{{ $label }}</label>
                    @endif

                    <!-- PROGRESS BAR  -->
                    @if(! $hideProgress)
                        <div class="h-1 -mt-5 mb-5">
                            <progress
                                :value="progress"
                                wire:loading
                                wire:target="{{ $modelName() }}"
                                max="100"
                                class="progress progress-success h-1 w-56"></progress>
                        </div>
                    @endif

                    <!-- FILE INPUT -->
                    <input
                        id="{{ $uuid }}"
                        type="file"
                        {{
                            $attributes->class([
                                "file-input file-input-bordered file-input-primary",
                                "hidden" => $slot->isNotEmpty()
                            ])
                        }}
                    />

                    @if ($slot->isNotEmpty())
                        <label for="{{ $uuid }}">{{ $slot }}</label>
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
