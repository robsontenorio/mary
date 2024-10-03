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
        public mixed $expandableCondition = null,
        public ?string $link = null,
        public ?bool $withPagination = false,
        public ?string $perPage = null,
        public ?array $perPageValues = [10, 20, 50, 100],
        public ?array $sortBy = [],
        public ?array $rowDecoration = [],
        public ?array $cellDecoration = [],
        public ?bool $showEmptyText = false,
        public mixed $emptyText = 'No records found.',
        public string $containerClass = 'overflow-x-auto',
        public ?bool $noHover = false,

        // Slots
        public mixed $actions = null,
        public mixed $tr = null,
        public mixed $cell = null,
        public mixed $expansion = null,
        public mixed $empty = null,

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
        if (is_array($this->rows)) {
            return collect($this->rows)->pluck($this->selectableKey)->all();
        }

        return $this->rows->pluck($this->selectableKey)->all();
    }

    // Check if header is sortable
    public function isSortable(mixed $header): bool
    {
        return count($this->sortBy) && ($header['sortable'] ?? true);
    }

    // Check if header is hidden
    public function isHidden(mixed $header): bool
    {
        return $header['hidden'] ?? false;
    }

    // Check if link should be shown in cell
    public function hasLink(mixed $header): bool
    {
        return $this->link && empty($header['disableLink']);
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
            ? ($this->sortBy['direction'] == 'asc') ? 'desc' : 'asc'
            : 'asc';

        return ['column' => $header['sortBy'] ?? $header['key'], 'direction' => $direction];
    }

    // Build row link
    public function redirectLink(mixed $row): string
    {
        $link = $this->link;

        // Transform from `route()` pattern
        $link = Str::of($link)->replace('%5B', '{')->replace('%5D', '}');

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

    public function selectableModifier(): string
    {
        return is_string($this->getAllIds()[0] ?? null) ? "" : ".number";
    }

    public function getKeyValue($row, $key): mixed
    {
        $value = data_get($row, $this->$key);

        return is_numeric($value) && ! str($value)->startsWith('0') ? $value : "'$value'";
    }

    public function render(): View|Closure|string
    {
        return <<<'HTML'
                <div x-data="{
                                selection: @entangle($attributes->wire('model')),
                                pageIds: {{ json_encode($getAllIds()) }},
                                isSelectable: {{ json_encode($selectable) }},
                                colspanSize: 0,
                                init() {
                                    this.colspanSize = $refs.headers.childElementCount

                                    if (this.isSelectable) {
                                        this.handleCheckAll()
                                    }
                                },
                                isExpanded(key) {
                                    return this.selection.includes(key)
                                },
                                isPageFullSelected() {
                                    return [...this.selection]
                                                .sort((a, b) => b - a)
                                                .toString()
                                                .includes([...this.pageIds].sort((a, b) => b - a).toString())
                                },
                                toggleCheck(checked, content) {
                                    this.$dispatch('row-selection', { row: content, selected: checked });
                                    this.handleCheckAll()
                                },
                                toggleCheckAll(checked) {
                                    checked ? this.pushIds() : this.removeIds()
                                },
                                toggleExpand(key) {
                                     this.selection.includes(key)
                                        ? this.selection = this.selection.filter(i => i !== key)
                                        : this.selection.push(key)
                                },
                                pushIds() {
                                    this.selection.push(...this.pageIds.filter(i => !this.selection.includes(i)))
                                },
                                removeIds() {
                                    this.selection =  this.selection.filter(i => !this.pageIds.includes(i) )
                                },
                                handleCheckAll() {
                                    this.$nextTick(() => {
                                            this.isPageFullSelected()
                                                ? this.$refs.mainCheckbox.checked = true
                                                : this.$refs.mainCheckbox.checked = false
                                        })
                                }
                             }"
                >
                <div class="{{ $containerClass }}" x-classes="overflow-x-auto">
                <table
                        {{
                            $attributes
                                ->whereDoesntStartWith('wire:model')
                                ->class([
                                    'table',
                                    'table-zebra' => $striped,
                                    '[&_tr:nth-child(4n+3)]:bg-base-200' => $striped && $expandable,
                                    'cursor-pointer' => $attributes->hasAny(['@row-click', 'link'])
                                ])
                        }}
                    >
                        <!-- HEADERS -->
                        <thead @class(["text-black dark:text-gray-200", "hidden" => $noHeaders])>
                            <tr x-ref="headers">
                                <!-- CHECKALL -->
                                @if($selectable)
                                    <th class="w-1" wire:key="{{ $uuid }}-checkall-{{ implode(',', $getAllIds()) }}">
                                        <input
                                            id="checkAll-{{ $uuid }}"
                                            type="checkbox"
                                            class="checkbox checkbox-sm"
                                            x-ref="mainCheckbox"
                                            @click="toggleCheckAll($el.checked)" />
                                    </th>
                                @endif

                                <!-- EXPAND EXTRA HEADER -->
                                @if($expandable)
                                    <th class="w-1"></th>
                                 @endif

                                @foreach($headers as $header)
                                     @php
                                        # SKIP THE HIDDEN COLUMN
                                        if($isHidden($header)) continue;

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
                                <tr
                                    wire:key="{{ $uuid }}-{{ $k }}"
                                    @class([$rowClasses($row), "hover:bg-base-200/50" => !$noHover])
                                    @if($attributes->has('@row-click'))
                                        @click="$dispatch('row-click', {{ json_encode($row) }});"
                                    @endif
                                >
                                    <!-- CHECKBOX -->
                                    @if($selectable)
                                        <td class="w-1">
                                            <input
                                                id="checkbox-{{ $uuid }}-{{ $k }}"
                                                type="checkbox"
                                                class="checkbox checkbox-sm checkbox-primary"
                                                value="{{ data_get($row, $selectableKey) }}"
                                                x-model{{ $selectableModifier() }}="selection"
                                                @click="toggleCheck($el.checked, {{ json_encode($row) }})" />
                                        </td>
                                    @endif

                                    <!-- EXPAND ICON -->
                                    @if($expandable)
                                        <td class="w-1 pe-0">
                                            @if(data_get($row, $expandableCondition))
                                                <x-mary-icon
                                                    name="o-chevron-down"
                                                    ::class="isExpanded({{ $getKeyValue($row, 'expandableKey') }}) || '-rotate-90 !text-current'"
                                                    class="cursor-pointer p-2 w-8 h-8 bg-base-300 rounded-lg"
                                                    @click="toggleExpand({{ $getKeyValue($row, 'expandableKey') }});" />
                                            @endif
                                        </td>
                                     @endif

                                    <!--  ROW VALUES -->
                                    @foreach($headers as $header)
                                        @php
                                            # SKIP THE HIDDEN COLUMN
                                            if($isHidden($header)) continue;

                                            # Scoped slot`s name like `user.city` are compiled to `user___city` through `@scope / @endscope`.
                                            # So we use current `$header` key  to find that slot on context.
                                            $temp_key = str_replace('.', '___', $header['key'])
                                        @endphp

                                        <!--  HAS CUSTOM SLOT ? -->
                                        @if(isset(${"cell_".$temp_key}))
                                            <td @class([$cellClasses($row, $header), "p-0" => $hasLink($header)])>
                                                @if($hasLink($header))
                                                    <a href="{{ $redirectLink($row) }}" wire:navigate class="block py-3 px-4">
                                                @endif

                                                {{ ${"cell_".$temp_key}($row)  }}

                                                @if($hasLink($header))
                                                    </a>
                                                 @endif
                                            </td>
                                        @else
                                            <td @class([$cellClasses($row, $header), "p-0" => $hasLink($header)])>
                                                @if($hasLink($header))
                                                    <a href="{{ $redirectLink($row) }}" wire:navigate class="block py-3 px-4">
                                                @endif

                                                {{ data_get($row, $header['key']) }}

                                                @if($hasLink($header))
                                                    </a>
                                                @endif
                                            </td>
                                        @endif
                                    @endforeach

                                    <!-- ACTIONS -->
                                    @if($actions)
                                        <td class="text-right py-0">{{ $actions($row) }}</td>
                                    @endif
                                </tr>

                                <!-- EXPANSION SLOT -->
                                @if($expandable)
                                    <tr wire:key="{{ $uuid }}-{{ $k }}--expand" class="!bg-inherit" :class="isExpanded({{ $getKeyValue($row, 'expandableKey') }}) || 'hidden'">
                                        <td :colspan="colspanSize">
                                            {{ $expansion($row) }}
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>

                    @if(count($rows) === 0)
                        @if($showEmptyText)
                            <div class="text-center py-4 text-gray-500 dark:text-gray-400">
                                {{ $emptyText }}
                            </div>
                        @endif
                        @if($empty)
                            <div class="text-center py-4 text-gray-500 dark:text-gray-400">
                                {{ $empty }}
                            </div>
                        @endif
                    @endif
                </div>
                    <!-- Pagination -->
                    @if($withPagination)
                        @if($perPage)
                            <x-mary-pagination :rows="$rows" :per-page-values="$perPageValues" wire:model.live="{{ $perPage }}" />
                        @else
                            <x-mary-pagination :rows="$rows" :per-page-values="$perPageValues" />
                        @endif
                    @endif
                </div>
            HTML;
    }
}
