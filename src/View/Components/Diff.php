<?php

namespace Mary\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Jfcherng\Diff\DiffHelper;

class Diff extends Component
{
    public string $uuid;

    public function __construct(
        public string $old = '',
        public string $new = '',
        public string $fileName = 'payload.json',
        public ?array $config = []
    ) {
        $this->uuid = "mary" . md5(serialize($this));
    }

    public function setup(): string
    {
        return json_encode(array_merge([
            'drawFileList' => false,
            'matching' => 'lines',
            'outputFormat' => 'side-by-side',
            'synchronisedScroll' => true,
            'fileContentToggle' => false,
        ], $this->config));
    }

    public function diff(): string
    {
        $diff = DiffHelper::calculate($this->old . '
', $this->new . '
');

        return "--- {$this->fileName}\n+++ {$this->fileName}\n" . $diff;
    }

    public function render(): View|Closure|string
    {
        return <<<'HTML'
            <div
                x-data="{
                        init(){
                           var diff = new Diff2HtmlUI($refs.diff{{ $uuid }}, `{{ $diff() }}`, {{ $setup() }});
                           diff.draw();
                        }
                }"
             >
                <div x-ref="diff{{ $uuid }}" class="[&_.d2h-diff-table]:text-xs [&_.d2h-file-header]:bg-base-100 [&_.d2h-file-wrapper]:border-dashed [&_.d2h-file-wrapper]:border [&_.d2h-file-wrapper]:bg-base-100 [&_.d2h-del]:bg-red-50 [&_.d2h-ins]:bg-green-50 [&_.d2h-code-line-ctn]:whitespace-pre-wrap [&_.d2h-code-side-line]:w-auto">
                </div>
            </div>
        HTML;
    }
}
