<?php

namespace Mary\View\Components;

use ArrayAccess;
use Closure;
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

        // Slots
        public mixed $actions = null,
        public mixed $tr = null,
        public mixed $cell = null
    ) {
        $this->uuid = md5(serialize($this));
    }

    public function render(): View|Closure|string
    {
        return <<<'HTML'
                <div class="overflow-x-auto">
                    <table
                        {{ $attributes->class([
                                'table',
                                'table-zebra' => $striped,
                                'cursor-pointer' => $attributes->has('@row-click')])
                        }}
                    >
                        <!-- HEADERS -->
                        <thead @class(["text-black dark:text-gray-500", "hidden" => $noHeaders])>
                            <tr>
                                @foreach($headers as $header)
                                     @php
                                        # Scoped slot`s name like `user.city` are compiled to `user___city` through `@scope / @endscope`.
                                        # So we use current `$header` key  to find that slot on context.
                                        $temp_key = str_replace('.', '___', $header['key'])
                                    @endphp

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
                                @foreach($headers as $header)
                                    @php
                                        # Scoped slot`s name like `user.city` are compiled to `user___city` through `@scope / @endscope`.
                                        # So we use current `$header` key  to find that slot on context.
                                        $temp_key = str_replace('.', '___', $header['key'])
                                    @endphp

                                    @if(isset(${"cell_".$temp_key}))
                                        <td>
                                            {{ ${"cell_".$temp_key}($row)  }}
                                        </td>
                                    @else
                                        <td>
                                            {{ data_get($row, $header['key']) }}
                                        </td>
                                    @endif
                                @endforeach

                                <!-- ACTIONS -->
                                @if($actions)
                                    <td class="text-right" @click="event.stopPropagation()">{{ $actions($row) }}</td>
                                @endif
                                </tr>
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
