<?php

namespace Mary\View\Components;

use ArrayAccess;
use Closure;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Illuminate\View\Component;

class Table extends Component
{
    public string $uuid;

    public function __construct(
        public array $headers,
        public ArrayAccess|array $rows,
        public ?bool $striped = false,
        public ?bool $noHeaders = false,
        public ?bool $selectable = false,
        public ?string $selectableKey = 'id',
        public ?bool $expandable = false,
        public ?string $expandableKey = 'id',
        public ?string $link = null,

        // Slots
        public mixed $actions = null,
        public mixed $tr = null,
        public mixed $cell = null,
        public mixed $expansion = null,
    ) {
        $this->uuid = md5(serialize($this));

        if ($this->selectable && $this->expandable) {
            throw new Exception("You can not combine `expandable` with `selectable`.");
        }
    }

    // Get all ids for selectable and expandable features
    public function getAllIds(): array
    {
        return collect($this->rows)->pluck($this->selectableKey)->all();
    }

    // Build row link
    public function redirectLink(mixed $row): string
    {
        $link = $this->link;

        // Extract tokens like {id}, {city.name} ...
        $tokens = Str::of($link)->matchAll('/\{(.*?)\}/');

        // Replace tokens by actual row values
        $tokens->each(function (string $token) use ($row, &$link) {
            $link = Str::of($link)->replace("{" . $token . "}", data_get($row, $token))->toString();
        });

        return $link;
    }

    public function render(): View|Closure|string
    {
        return <<<'HTML'
                <div x-data="{
                                selection: @entangle($attributes->wire('model')),
                                colspanSize: 0,
                                toggleSelection(checked){
                                    checked ? this.selection = @js($getAllIds()) : this.selection = []
                                },
                                toggleExpand(key){
                                     this.selection.includes(key)
                                        ? this.selection = this.selection.filter(i => i !== key)
                                        : this.selection.push(key)
                                },
                                isExpanded(key){
                                    return this.selection.includes(key)
                                },
                                init() {
                                    this.colspanSize = $refs.headers.childElementCount
                                }
                             }"
                                class="overflow-x-auto"
                >
                    <table
                        {{
                            $attributes
                                ->except('wire:model')
                                ->class([
                                    'table',
                                    'table-zebra' => $striped,
                                    'cursor-pointer' => $attributes->hasAny(['@row-click', 'link'])
                                ])
                        }}
                    >
                        <!-- HEADERS -->
                        <thead @class(["text-black dark:text-gray-500", "hidden" => $noHeaders])>
                            <tr x-ref="headers">
                                <!-- CHECKBOX -->
                                @if($selectable)
                                    <th class="w-1">
                                        <input
                                            type="checkbox"
                                            class="checkbox checkbox-sm"
                                            x-ref="mainCheckbox"
                                            @click="toggleSelection($el.checked)" />
                                    </th>
                                @endif

                                <!-- EXPAND EXTRA HEADER -->
                                @if($expandable)
                                    <th class="w-1"></th>
                                 @endif

                                @foreach($headers as $header)
                                     @php
                                        # Scoped slot`s name like `user.city` are compiled to `user___city` through `@scope / @endscope`.
                                        # So we use current `$header` key  to find that slot on context.
                                        $temp_key = str_replace('.', '___', $header['key'])
                                    @endphp

                                    <!--  HAS CUSTOM SLOT ? -->
                                    @if(isset(${"header_".$temp_key}))
                                        <th class="{{ $header['class'] ?? ' ' }}">
                                            {{ ${"header_".$temp_key}($header)  }}
                                        </th>
                                    @else
                                        <th class="{{ $header['class'] ?? ' ' }}">
                                            {{ $header['label'] }}
                                        </th>
                                    @endif
                                @endforeach

                                <!-- ACTIONS (Just a empty column) -->
                                @if($actions)
                                    <th class="w-1"></th>
                                @endif
                            </tr>
                        </thead>

                        <!-- ROWS -->
                        <tbody>
                            @foreach($rows as $k => $row)
                                <tr wire:key="{{ $uuid }}-{{ $k }}" class="hover:bg-base-200/50" @click="$dispatch('row-click', {{ json_encode($row) }});">

                                    <!-- CHECKBOX -->
                                    @if($selectable)
                                        <td class="w-1">
                                            <input
                                                type="checkbox"
                                                class="checkbox checkbox-sm checkbox-primary"
                                                value="{{ data_get($row, $selectableKey) }}"
                                                x-model="selection"
                                                @click="$dispatch('row-selection', { row: {{ json_encode($row) }}, selected: $el.checked }); $refs.mainCheckbox.checked = false" />
                                        </td>
                                    @endif

                                    <!-- EXPAND ICON -->
                                    @if($expandable)
                                        <td class="w-1 pr-0">
                                            <x-icon
                                                name="o-chevron-down"
                                                ::class="isExpanded({{ data_get($row, $expandableKey) }}) || '-rotate-90 !text-current !bg-base-200'"
                                                class="cursor-pointer p-2 w-8 h-8 bg-base-300 rounded-lg"
                                                @click="toggleExpand({{ data_get($row, $expandableKey) }});" />
                                        </td>
                                     @endif

                                    <!--  ROW VALUES -->
                                    @foreach($headers as $header)
                                        @php
                                            # Scoped slot`s name like `user.city` are compiled to `user___city` through `@scope / @endscope`.
                                            # So we use current `$header` key  to find that slot on context.
                                            $temp_key = str_replace('.', '___', $header['key'])
                                        @endphp

                                        <!--  HAS CUSTOM SLOT ? -->
                                        @if(isset(${"cell_".$temp_key}))
                                            <td @class(["p-0" => $link])>
                                                @if($link)
                                                    <a href="{{ $redirectLink($row) }}" wire:navigate class="block p-4">
                                                @endif

                                                {{ ${"cell_".$temp_key}($row)  }}

                                                @if($link)
                                                    </a>
                                                 @endif
                                            </td>
                                        @else
                                            <td @class(["p-0" => $link, "hidden" => Str::contains($header['class'] ?? '', 'hidden') ])>

                                                @if($link)
                                                    <a href="{{ $redirectLink($row) }}" wire:navigate class="block p-4">
                                                @endif

                                                {{ data_get($row, $header['key']) }}

                                                @if($link)
                                                    </a>
                                                @endif
                                            </td>
                                        @endif
                                    @endforeach

                                    <!-- ACTIONS -->
                                    @if($actions)
                                        <td class="text-right" @click="event.stopPropagation()">{{ $actions($row) }}</td>
                                    @endif
                                </tr>

                                <!-- EXPANSION SLOT -->
                                @if($expandable)
                                    <tr wire:key="{{ $uuid }}-{{ $k }}--expand" :class="isExpanded({{ data_get($row, $expandableKey) }}) || 'hidden'">
                                        <td :colspan="colspanSize">
                                            {{ $expansion($row) }}
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                    @php
                        # TODO: workaround for bug when using slot with @scope on tables.
                        # It seems it loses start/end context with @scope/@endscope. So I am just placing any hidden component here.
                    @endphp
                    <x-alert style="display:none" />
                </div>
            HTML;
    }
}
