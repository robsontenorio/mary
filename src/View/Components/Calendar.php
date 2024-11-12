<?php

namespace Mary\View\Components;

use Carbon\CarbonPeriod;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;
use Illuminate\View\Component;

class Calendar extends Component
{
    public string $uuid;

    public function __construct(
        public ?int $months = 1,
        public ?string $locale = 'en-EN',
        public ?bool $weekendHighlight = false,
        public ?bool $sundayStart = false,
        public ?array $config = [],
        public ?array $events = [],
    ) {
        $this->uuid = "mary" . md5(serialize($this));
    }

    public function setup(): string
    {
        $config = json_encode(array_merge([
            'type' => $this->months == 1 ? 'default' : 'multiple',
            'months' => $this->months,
            'jumpMonths' => $this->months,
            'popups' => $this->popups(),
            'settings' => [
                'lang' => $this->locale,
                'visibility' => [
                    'daysOutside' => false,
                    'weekend' => $this->weekendHighlight,
                ],
                'selection' => [
                    'day' => false,
                ],
                'iso8601' => ! $this->sundayStart,
            ],
            'CSSClasses' => 'y',
            'actions' => 'x',
        ], $this->config));

        $config = $this->addCss($config);

        return $config;
    }

    // Extra CSS for responsive layout
    public function addCss(string $config): string
    {
        return str_replace('"y"', '{"grid":"vanilla-calendar-grid flex flex-wrap justify-around","calendar":"vanilla-calendar"}', $config);
    }

    public function popups()
    {
        $buffer = [];

        return collect($this->events)->flatMap(function ($event) use (&$buffer) {
            if ($range = $event['range'] ?? []) {
                $dates = [];

                $period = CarbonPeriod::create($range[0], $range[1]);

                foreach ($period as $date) {
                    $dates[] = Carbon::parse($date)->format('Y-m-d');
                }
            }

            if (isset($event['date'])) {
                $dates = [Carbon::parse($event['date'])->format('Y-m-d')];
            }

            return collect($dates)->flatMap(function ($date) use ($event, &$buffer) {
                $html = '<div><strong>' . $event['label'] . '</strong></div><div>' . ($event['description'] ?? null) . '</div><hr class="my-3 last:hidden" />';

                $buffer[$date] = ($buffer[$date] ?? '') . $html;

                return [
                    $date => [
                        'modifier' => $event['css'],
                        'html' => $buffer[$date]
                    ],
                ];
            });
        });
    }

    public function render(): View|Closure|string
    {
        return <<<'HTML'
            <div wire:key="calendar-{{ rand() }}">
                <div x-data x-init="const calendar = new VanillaCalendar($el, {{ $setup() }}); calendar.init();" class="w-fit"></div>
            </div>
            HTML;
    }
}
