<?php

namespace Mary\View\Components;

use ArrayAccess;
use Closure;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\View\Component;

class Table extends Component
{
    public string $uuid;

    public mixed $loop = null;

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
        public ?bool $withPagination = false,
        public ?array $sortBy = [],
        public ?array $rowDecoration = [],
        public ?array $cellDecoration = [],

        // Slots
        public mixed $actions = null,
        public mixed $tr = null,
        public mixed $cell = null,
        public mixed $expansion = null
    ) {
        if ($this->selectable && $this->expandable) {
            throw new Exception("You can not combine `expandable` with `selectable`.");
        }

        // Temp decoration
        $rowDecoration = $this->rowDecoration;
        $cellDecoration = $this->cellDecoration;

        // Remove decoration from serialization, because they are closures.
        unset($this->rowDecoration);
        unset($this->cellDecoration);

        // Serialize
        $this->uuid = "mary" . md5(serialize($this));

        // Put them back
        $this->rowDecoration = $rowDecoration;
        $this->cellDecoration = $cellDecoration;
    }

    // Get all ids for selectable and expandable features
    public function getAllIds(): array
    {
        // Pagination
        if ($this->rows instanceof ArrayAccess) {
            return $this->rows->pluck($this->selectableKey)->all();
        }

        return collect($this->rows)->pluck($this->selectableKey)->all();
    }

    // Check if header is sortable
    public function isSortable(mixed $header): bool
    {
        return count($this->sortBy) && ($header['sortable'] ?? true);
    }

    // Check if is currently sorted by this header
    public function isSortedBy(mixed $header): bool
    {
        if (count($this->sortBy) == 0) {
            return false;
        }

        return $this->sortBy['column'] == ($header['sortBy'] ?? $header['key']);
    }

    // Handle header sort
    public function getSort(mixed $header): mixed
    {
        if (! $this->isSortable($header)) {
            return false;
        }

        if (count($this->sortBy) == 0) {
            return ['column' => '', 'direction' => ''];
        }

        $direction = $this->isSortedBy($header)
            ? $this->sortBy['direction'] == 'asc' ? 'desc' : 'asc'
            : 'asc';

        return ['column' => $header['sortBy'] ?? $header['key'], 'direction' => $direction];
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

    public function rowClasses(mixed $row): ?string
    {
        $classes = [];

        foreach ($this->rowDecoration as $class => $condition) {
            if ($condition($row)) {
                $classes[] = $class;
            }
        }

        return Arr::join($classes, ' ');
    }

    public function cellClasses(mixed $row, array $header): ?string
    {
        $classes = Str::of($header['class'] ?? '')->explode(' ')->all();

        foreach ($this->cellDecoration[$header['key']] ?? [] as $class => $condition) {
            if ($condition($row)) {
                $classes[] = $class;
            }
        }

        return Arr::join($classes, ' ');
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
                        <thead @class(["text-black dark:text-gray-200", "hidden" => $noHeaders])>
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

                                    <th
                                        class="@if($isSortable($header)) cursor-pointer hover:bg-base-200 @endif {{ $header['class'] ?? ' ' }}"

                                        @if($sortBy && $isSortable($header))
                                            @click="$wire.set('sortBy', {column: '{{ $getSort($header)['column'] }}', direction: '{{ $getSort($header)['direction'] }}' })"
                                        @endif
                                    >
                                        {{ isset(${"header_".$temp_key}) ? ${"header_".$temp_key}($header) : $header['label'] }}

                                        @if($isSortable($header) && $isSortedBy($header))
                                            <x-mary-icon :name="$getSort($header)['direction'] == 'asc' ? 'o-arrow-small-down' : 'o-arrow-small-up'"  class="w-4 h-4 mb-1" />
                                        @endif
                                    </th>
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
                                @php
                                    # helper variable to provide the loop context
                                    $this->loop = $loop;
                                @endphp

                                <tr wire:key="{{ $uuid }}-{{ $k }}" class="hover:bg-base-200/50 {{ $rowClasses($row) }}" @click="$dispatch('row-click', {{ json_encode($row) }});">
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
                                            <x-mary-icon
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
                                            <td @class([$cellClasses($row, $header), "p-0" => $link])>
                                                @if($link)
                                                    <a href="{{ $redirectLink($row) }}" wire:navigate class="block py-3 px-4">
                                                @endif

                                                {{ ${"cell_".$temp_key}($row)  }}

                                                @if($link)
                                                    </a>
                                                 @endif
                                            </td>
                                        @else
                                            <td @class([$cellClasses($row, $header), "p-0" => $link])>
                                                @if($link)
                                                    <a href="{{ $redirectLink($row) }}" wire:navigate class="block py-3 px-4">
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
                                        <td class="text-right py-0" @click="event.stopPropagation()">{{ $actions($row) }}</td>
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

                    <!-- Pagination -->
                    @if($withPagination)
                        <div class="mary-table-pagination">
                            <div class="border border-x-0 border-t-0 border-b-1 border-b-base-300 mb-5"></div>

                            {{ $rows->onEachSide(1)->links(data: ['scrollTo' => false])  }}
                        </div>
                    @endif
                </div>
            HTML;
    }
}
