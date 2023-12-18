<?php

namespace Mary\Traits;

use Exception;
use Illuminate\Support\Str;

trait WithHtmlId
{
    public string $htmlId = 'element';

    public function setHtmlId(string $prefix, ?string $value = null): void
    {
        if (preg_match('/^[a-z]/i', $prefix) !== 1) {
            throw new Exception('Prefix must start with a letter.');
        }

        $this->htmlId = sprintf(
            '%s-%s',
            $prefix,
            $value ?? $this->uuid ?? Str::uuid(),
        );
    }
}
