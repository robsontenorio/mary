<?php

namespace Mary\Traits;

use Illuminate\Support\ViewErrorBag;

/**
 * @mixin \Illuminate\View\Component
 */
trait HasErrors
{

    public function modelName(): ?string
    {
        return $this->attributes->whereStartsWith('wire:model')->first();
    }

    public function errorBagName(): ?string
    {
        return $this->attributes->get('error-bag', $this->modelName());
    }

    public function errorClass(): ?string
    {
        return $this->attributes->get('error-class', 'text-red-500 label-text-alt p-1');
    }

    protected function shouldOmitError(ViewErrorBag $errors): bool
    {
        return $this->attributes->get('omit-error', false) || !$errors->has($this->errorBagName());
    }

    public function errorTemplate(ViewErrorBag $errors): string
    {
        if ($this->shouldOmitError($errors)) {
            return '';
        }

        $errorMessages = $this->getErrorMessages($errors);
        return $this->formatErrorMessages($errorMessages);
    }

    protected function getErrorMessages(ViewErrorBag $errors): array
    {
        $errorBag = $errors->get($this->errorBagName());

        if ($this->attributes->get('first-error-only', false)) {
            return [array_shift($errorBag)];
        }

        return $errorBag;
    }

    protected function formatErrorMessages(array $messages): string
    {
        $lines = collect($messages)->flatten();
        if ($this->attributes->get('first-error-only', false)) {
            $lines = $lines->slice(0, 1);
        }
        return $lines->map(function ($message) {
            return sprintf('<div class="%s">%s</div>', $this->errorClass(), $message);
        })->implode('');
    }

}
