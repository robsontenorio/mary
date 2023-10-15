<?php

namespace Mary\View\Components;

use ArrayAccess;
use Closure;
use Exception;
use Illuminate\Contracts\View\View;
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

    public function getAllIds(): array
    {
        return collect($this->rows)->pluck($this->selectableKey)->all();
    }

    public function colspanSize(): int
    {
        return count($this->headers) + ($this->selectable ? 1 : 0) + ($this->expandable ? 1 : 0) + ($this->actions == 1 ? 1 : 0);
    }

    public function render(): View|Closure|string
    {
        return <<<'HTML'
                <div x-data="{
                                selection: @entangle($attributes->wire('model')),
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
                                    'cursor-pointer' => $attributes->has('@row-click')
                                ])
                        }}
                    >
                        <!-- HEADERS -->
                        <thead @class(["text-black dark:text-gray-500", "hidden" => $noHeaders])>
                            <tr>
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
                            @foreach($rows as $row)
                                <tr class="hover:bg-base-200/50" @click="$dispatch('row-click', @js($row));">

                                    <!-- CHECKBOX -->
                                    @if($selectable)
                                        <td class="w-1">
                                            <input
                                                type="checkbox"
                                                class="checkbox checkbox-sm checkbox-primary"
                                                value="{{ data_get($row, $selectableKey) }}"
                                                x-model="selection"
                                                @click="$dispatch('row-selection', { row: @js($row), selected: $el.checked }); $refs.mainCheckbox.checked = false" />
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
                                            <td>
                                                {{ ${"cell_".$temp_key}($row)  }}
                                            </td>
                                        @else
                                            <td @class(["hidden" => Str::contains($header['class'] ?? '', 'hidden') ])>
                                                {{ data_get($row, $header['key']) }}
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
                                    <tr :class="isExpanded({{ data_get($row, $expandableKey) }}) || 'hidden'">
                                        <td colspan="{{ $colspanSize() }}">
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
