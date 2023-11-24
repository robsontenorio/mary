<?php

namespace Mary\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Tags extends Component
{
    public string $uuid;
    public function __construct(
        public ?string $label = null,
        public ?string $hint = null,
    ) {
        $this->uuid = md5(serialize($this));
    }

    public function modelName(): ?string
    {
        return $this->attributes->whereStartsWith('wire:model')->first();
    }

    public function render(): View|Closure|string
    {
        return <<<'HTML'
            <div x-data="{
                init() {
                    if (this.tags == null || !Array.isArray(this.tags)) {
                        this.tags = [];
                    }
                },
                tags: @entangle($attributes->wire('model')),
                tag: null,

                push() {
                    if (this.tag != '' && this.tag != null && this.tag != undefined) {
                        let tag = this.tag.trim()
                        tag = this.tag.replace(/,/g, '');
                        if (tag != '' && !this.hasTag(tag)) {
                            this.tags.push(tag)
                        }
                    }
                    this.clear()
                },

                hasTag(tag) {
                    var tag = this.tags.find(e => {
                        return e.toLowerCase() === tag.toLowerCase()
                    })
                    return tag != undefined
                },

                remove(index) {
                    this.tags.splice(index, 1)
                },

                clear() {
                    this.tag = null;
                },

                clearAll() {
                    this.tags = [];
                }
            }" x-on:keydown.escape="clear()">
                <!-- LABEL -->
                <label class="label label-text pt-0 font-semibold" for="{{ $uuid }}">{{ $label }}</label>

                <!-- DIV AS INPUT -->
                <div class="input input-primary relative flex h-fit w-full max-w-full place-items-center justify-between [outline:none!important]">
                    <div class="py-1">
                        <template :key="index" x-for="(tag, index) in tags">
                            <div class="my-1 mr-2 inline-block cursor-pointer space-x-2 rounded bg-primary/20 px-2 py-1 transition-all hover:bg-primary/40">
                                <span x-text="tag"></span>
                                <svg class="inline-block h-4 w-4 stroke-current transition-all hover:text-red-500" fill="none" viewBox="0 0 24 24" x-on:click="remove(index)" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M6 18L18 6M6 6l12 12" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path>
                                </svg>
                            </div>
                        </template>

                        <!-- INPUT -->
                        <input class="input input-bordered input-ghost input-sm my-1 rounded transition-all focus:input-primary" id="{{ $uuid }}" placeholder="{{ $attributes->whereStartsWith('placeholder')->first() }}" type="text" x-model="tag" x-on:click.outside="clear()" x-on:keydown.enter.prevent="push()" x-on:keyup.prevent="if (event.key === ',') { push() }" />
                    </div>

                    <svg class="inline-block h-6 w-6 min-w-fit cursor-pointer stroke-current transition-all hover:text-red-500" fill="none" viewBox="0 0 24 24" x-on:click="clearAll()" xmlns="http://www.w3.org/2000/svg">
                        <path d="M6 18L18 6M6 6l12 12" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path>
                    </svg>
                </div>

                <!-- ERROR -->
                @error($modelName())
                    <div class="label-text-alt p-1 text-red-500">{{ $message }}</div>
                @enderror

                <!-- HINT -->
                @if ($hint)
                    <div class="label-text-alt p-1 pb-0 text-gray-400">{{ $hint }}</div>
                @endif
            </div>
        HTML;
    }
}
