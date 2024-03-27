<?php

namespace Mary\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;

class ToastException extends Exception
{
    protected string $type = 'info';

    protected ?string $title = null;

    protected ?string $description = null;

    protected string $position = 'toast-top toast-end';

    protected string $icon = 'o-information-circle';

    protected string $css = 'alert-info';

    protected int $timeout = 3000;

    protected bool $preventDefault = true;

    public static function typedMessage(
        string $type,
        string $title,
        string $description = null,
        string $position = 'toast-top toast-end',
        string $icon = 'o-information-circle',
        string $css = 'alert-info',
        int $timeout = 3000
    ): self {
        $instance = new self(message: $title, code: 500);

        $instance->type = $type;
        $instance->title = $title;
        $instance->description = $description;
        $instance->position = $position;
        $instance->icon = $icon;
        $instance->css = $css;
        $instance->timeout = $timeout;

        return $instance;
    }

    public static function info(
        string $title,
        string $description = null,
        string $position = 'toast-top toast-end',
        string $icon = 'o-information-circle',
        string $css = 'alert-info',
        int $timeout = 3000,
    ): self {
        return self::typedMessage(
            type: 'info',
            title: $title,
            description: $description,
            position: $position,
            icon: $icon,
            css: $css,
            timeout: $timeout
        );
    }

    public static function success(
        string $title,
        string $description = null,
        string $position = 'toast-top toast-end',
        string $icon = 'o-check-circle',
        string $css = 'alert-success',
        int $timeout = 3000,
    ): self {
        return self::typedMessage(
            type: 'success',
            title: $title,
            description: $description,
            position: $position,
            icon: $icon,
            css: $css,
            timeout: $timeout
        );
    }

    public static function error(
        string $title,
        string $description = null,
        string $position = 'toast-top toast-end',
        string $icon = 'o-x-circle',
        string $css = 'alert-error',
        int $timeout = 3000,
    ): self {
        return self::typedMessage(
            type: 'error',
            title: $title,
            description: $description,
            position: $position,
            icon: $icon,
            css: $css,
            timeout: $timeout
        );
    }

    public static function warning(
        string $title,
        string $description = null,
        string $position = 'toast-top toast-end',
        string $icon = 'o-exclamation-triangle',
        string $css = 'alert-warning',
        int $timeout = 3000,
    ): self {
        return self::typedMessage(
            type: 'warning',
            title: $title,
            description: $description,
            position: $position,
            icon: $icon,
            css: $css,
            timeout: $timeout
        );
    }

    public function permitDefault(): self
    {
        $this->preventDefault = false;

        return $this;
    }

    public function render(Request $request): JsonResponse|false
    {
        if ($request->hasHeader('x-livewire')) {
            return response()->json([
                'toast' => [
                    'type' => $this->type,
                    'title' => $this->title,
                    'description' => $this->description,

                    'position' => $this->position,
                    'icon' => Blade::render("<x-mary-icon class='w-7 h-7' name='" . $this->icon . "' />"),
                    'css' => $this->css,
                    'timeout' => $this->timeout,
                ],
                'prevent_default' => $this->preventDefault
            ], $this->getCode());
        }

        return false;
    }
}
