<?php

namespace Mary\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Textarea extends Component
{
    public string $uuid;

    public function __construct(
        public ?string $label = null,
        public ?string $hint = null,
        public ?bool $inline = false,
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
            <div>
                <!-- STANDARD LABEL -->
                @if($label && !$inline)
                    <label class="label label-text font-semibold">{{ $label }}</label>
                @endif

                <div class="flex-1 relative">
                    <!-- INPUT -->
                    <textarea
                        placeholder = "{{ $attributes->whereStartsWith('placeholder')->first() }} "

                        {{
                            $attributes
                            ->class([
                                'textarea textarea-primary w-full peer',
                                'pt-5' => ($inline && $label),
                                'border border-dashed' => $attributes->has('readonly') && $attributes->get('readonly') == true,
                                'textarea-error' => $errors->has($modelName())
                            ])
                        }}
                    >{{ $slot }}</textarea>

                    <!-- INLINE LABEL -->
                    @if($label && $inline)
                        <label for="{{ $uuid }}" class="absolute text-gray-400 duration-300 transform -translate-y-3 scale-75 top-4 bg-white rounded dark:bg-gray-900 px-2 peer-focus:px-2 peer-focus:text-blue-600 peer-focus:dark:text-blue-500 peer-placeholder-shown:scale-100 peer-placeholder-shown:-translate-y-1/2  peer-focus:scale-75 peer-focus:-translate-y-3 left-2">
                            {{ $label }}
                        </label>
                    @endif
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
