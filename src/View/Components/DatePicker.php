<?php
namespace Mary\View\Components;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Arr;
use Illuminate\View\Component;
class DatePicker extends Component
{
    public string $uuid;
    public function __construct(
        public ?string $label = null,
        public ?string $icon = null,
        public ?string $iconRight = null,
        public ?string $hint = null,
        public ?string $hintClass = 'fieldset-label',
        public ?bool $inline = false,
        public ?array $config = [],
        // Slots
        public mixed $prepend = null,
        public mixed $append = null,
        // Validations
        public ?string $errorField = null,
        public ?string $errorClass = 'text-error',
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
        // Handle `wire:model.live` for `range` dates
        if (isset($this->config["mode"]) && $this->config["mode"] == "range" && $this->attributes->wire('model')->hasModifier('live')) {
            $this->attributes->setAttributes([
                'wire:model' => $this->modelName(),
                'live' => true
            ]);
        }
        $config = json_encode(array_merge([
            'dateFormat' => 'Y-m-d H:i',
            'altInput' => true,
            'altInputClass' => ' ',
            'clickOpens' => ! $this->attributes->has('readonly') || $this->attributes->get('readonly') == false,
            'defaultDate' => '#model#',
            'animate' => false,
            'plugins' => ['#plugins#'],
        ], Arr::except($this->config, ["plugins"])));
        // Plugins
        $plugins = "";
        foreach (Arr::get($this->config, 'plugins', []) as $plugin) {
            $plugins .= "new " . key($plugin) . "( " . json_encode(current($plugin)) . " ),";
        }
        // Plugins
        $config = str_replace('"#plugins#"', $plugins, $config);
        // Sets default date as current bound model
        $config = str_replace('"#model#"', '$wire.get("' . $this->modelName() . '")', $config);
        return $config;
    }
    public function render(): View|Closure|string
    {
        return <<<'BLADE'
            <div>
                @php $uuid = $uuid . $modelName() @endphp
                <fieldset class="fieldset py-0">
                    @if($label && !$inline) <legend class="fieldset-legend mb-0.5"> {{ $label }} @if($attributes->get('required')) <span class="text-error">*</span> @endif </legend> @endif
                    <label @class(["floating-label" => $label && $inline])>
                        @if ($label && $inline) <span class="font-semibold">{{ $label }}</span> @endif
                        <div class="w-full">
                            @if($prepend) {{ $prepend }} @endif
                            <label {{ $attributes->whereStartsWith('class')->class([ "input w-full", "join-item" => $prepend || $append, "border-dashed" => $attributes->has("readonly") && $attributes->get("readonly") == true, "bg-base-300 opacity-40 border-0 shadow-none cursor-not-allowed" => $attributes->has("disabled") && $attributes->get("disabled") == true, "!input-error" => $errorFieldName() && $errors->has($errorFieldName()) && !$omitError ]) }} >
                                @if($icon) <x-mary-icon :name="$icon" class="pointer-events-none w-4 h-4 -ml-1 opacity-40" /> @endif
                                <div x-data="{instance: undefined}" x-init="instance = flatpickr($refs.input, {{ $setup() }});" @if(isset($config["mode"]) && $config["mode"] == "range" && $attributes->get('live')) @change="const value = $event.target.value; if(value.split('to').length == 2) { $wire.set('{{ $modelName() }}', value) };" @endif x-on:livewire:navigating.window="instance.destroy();" class="w-full" >
                                    <input x-ref="input" {{ $attributes->merge(['type' => 'date']) }} />
                                </div>
                                @if($iconRight) <x-mary-icon :name="$iconRight" class="pointer-events-none w-4 h-4 opacity-40" /> @endif
                            </label>
                            @if($append) {{ $append }} @endif
                        </div>
                    </label>
                    @if(!$omitError && $errors->has($errorFieldName()))
                        @foreach($errors->get($errorFieldName()) as $message) @foreach(Arr::wrap($message) as $line) <div class="{{ $errorClass }}" x-class="text-error">{{ $line }}</div> @break($firstErrorOnly) @endforeach @break($firstErrorOnly) @endforeach
                    @endif
                    @if($hint) <div class="{{ $hintClass }}" x-classes="fieldset-label">{{ $hint }}</div> @endif
                </fieldset>
            </div>
            BLADE;
    }
}
