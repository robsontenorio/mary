<?php

namespace Mary\Traits;

use Illuminate\Support\Str;

trait WithHtmlId
{
    public string $htmlId = 'element';

    public function setHtmlId(string $prefix, ?string $value = null): void
    {
        $this->htmlId = sprintf(
            '%s-%s',
            $prefix,
            $value ?? $this->uuid ?? Str::uuid(),
        );
    }
}
