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
        public ?string $icon = null,
        public ?string $iconRight = null,
        public ?string $hint = null,
        public ?string $prefix = null,
        public ?string $suffix = null,
        public ?bool $inline = false,

        // Slots
        public mixed $prepend = null,
        public mixed $append = null
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
            <div @tags-update="console.log('tags updated', $event.detail.tags)" @click.away="clearSearch()" @keydown.escape="clearSearch()"
                x-data="{
                    tags: @entangle($attributes->wire('model')),
                    myInput: '',
                    open: false,

                    addTag(tag) {
                        tag = tag.trim()
                        if (tag != '' && !this.hasTag(tag)) {
                            this.tags.push(tag)
                        }
                        this.clearSearch()
                        this.fireTagsUpdateEvent()
                    },
                    fireTagsUpdateEvent() {
                        this.$el.dispatchEvent(new CustomEvent('tags-update', {
                            detail: {
                                tags: this.tags
                            },
                            bubbles: true,
                        }));
                    },
                    hasTag(tag) {
                        var tag = this.tags.find(e => {
                            return e.toLowerCase() === tag.toLowerCase()
                        })
                        return tag != undefined
                    },
                    removeTag(index) {
                        this.tags.splice(index, 1)
                        this.fireTagsUpdateEvent()
                    },
                    search(q) {
                        if (q.includes(',')) {
                            q.split(',').forEach(function(val) {
                                this.addTag(val)
                            }, this)
                        }
                        this.toggleSearch()
                    },
                    clearSearch() {
                        this.myInput = ''
                        this.toggleSearch()
                    },
                    toggleSearch() {
                        this.open = this.myInput != ''
                    }
                }">
                
                <!-- STANDARD LABEL -->
                @if($label && !$inline)
                    <label for="{{ $uuid }}" class="pt-0 label label-text font-semibold">{{ $label }}</label>
                @endif

                <!-- PREFIX/SUFFIX/PREPEND/APPEND CONTAINER -->
                @if($prefix || $suffix || $prepend || $append)
                    <div class="flex">
                @endif

                <!-- PREFIX / PREPEND -->
                @if($prefix || $prepend)
                    <div class="rounded-l-lg flex items-center bg-base-200 @if($prefix) border border-primary border-r-0 px-4 @endif">
                        {{ $prepend ?? $prefix }}
                    </div>
                @endif
                
                <div class="flex-1 relative">
                    <!-- INPUT -->
                    <input
                        id="{{ $uuid }}"
                        placeholder = "{{ $attributes->whereStartsWith('placeholder')->first() }} "
                        x-ref="myInput"
                        x-model="myInput"
                        x-on:input="search($event.target.value)"

                        {{
                            $attributes
                                ->merge(['type' => 'text'])
                                ->except(['wire:model', 'required'])
                                ->class([
                                    'input input-primary w-full peer',
                                    'pl-10' => ($icon),
                                    'h-14' => ($inline),
                                    'pt-3' => ($inline && $label),
                                    'rounded-l-none' => $prefix || $prepend,
                                    'rounded-r-none' => $suffix || $append,
                                    'border border-dashed' => $attributes->has('readonly') && $attributes->get('readonly') == true,
                                    'input-error' => $errors->has($modelName())
                            ])
                        }}
                    />

                    <!-- ICON  -->
                    @if($icon)
                        <x-icon :name="$icon" class="absolute top-1/2 -translate-y-1/2 left-3 text-gray-400 pointer-events-none" />
                    @endif

                    <!-- RIGHT ICON  -->
                    @if($iconRight)
                        <x-icon :name="$iconRight" class="absolute top-1/2 right-3 -translate-y-1/2 text-gray-400 pointer-events-none" />
                    @endif

                    <!-- INLINE LABEL -->
                    @if($label && $inline)
                        <label for="{{ $uuid }}" class="absolute text-gray-400 duration-300 transform -translate-y-1 scale-75 top-2 origin-[0] rounded bg-base-100 px-2 peer-focus:px-2 peer-focus:text-blue-600 peer-focus:dark:text-blue-500 peer-placeholder-shown:scale-100 peer-placeholder-shown:-translate-y-1/2 peer-placeholder-shown:top-1/2 peer-focus:top-2 peer-focus:scale-75 peer-focus:-translate-y-1 @if($inline && $icon) left-9 @else left-3 @endif">
                            {{ $label }}
                        </label>
                    @endif
                </div>

                <!-- SUFFIX/APPEND -->
                @if($suffix || $append)
                    <div class="rounded-r-lg flex items-center bg-base-200 @if($suffix) border border-primary border-l-0 px-4 @endif">
                        {{ $append ?? $suffix }}
                    </div>
                @endif

                <!-- END: PREFIX/SUFFIX/APPEND/PREPEND CONTAINER  -->
                @if($prefix || $suffix || $prepend || $append)
                    </div>
                @endif

                <!-- ADD TAG -->
                <div :class="[open ? 'block' : 'hidden']" class="relative">
                    <div class="absolute left-0 z-40 mt-2 w-full">
                        <div class="rounded border border-primary bg-white py-1 text-sm shadow-lg dark:bg-gray-800">
                            <a class="block cursor-pointer px-4 py-1 text-gray-800 hover:bg-primary hover:text-white dark:text-white" x-on:click.prevent="addTag(myInput)">Add "<span class="font-semibold" x-text="myInput"></span>"</a>
                        </div>
                    </div>
                </div>

                <!-- TAGS -->
                <div class="mt-2" x-show="tags.length">
                    <template x-for="(tag, index) in tags">
                        <div class="btn btn-info btn-sm mb-2 mr-2">
                            <span x-text="tag"></span>
                            <button type="button" x-on:click.prevent="removeTag(index)">
                                <x-icon name="o-x-mark" class="w-4 h-4" />
                            </button>
                        </div>
                    </template>
                </div>

                <!-- ERROR -->
                @error($modelName())
                    <div class="text-red-500 label-text-alt p-1">{{ $message }}</div>
                @enderror

                <!-- HINT -->
                @if($hint)
                    <div class="label-text-alt text-gray-400 p-1 pb-0">{{ $hint }}</div>
                @endif
            </div>
        HTML;
    }
}
