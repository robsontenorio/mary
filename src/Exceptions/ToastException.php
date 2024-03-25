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

    protected string $position = 'toast-top toast-middle';

    protected string $icon = 'o-information-circle';

    protected string $css = 'alert-info';

    protected int $timeout = 3000;

    protected bool $preventDefault = true;

    public static function typedMessage(string $type, string $message, ?string $title = null): self
    {
        $instance = new self(message: $message, code: 500);
        $instance->type = $type;
        $instance->title = $title;
        return $instance;
    }

    public static function info(string $message, ?string $title = null, array $options = []): self
    {
        $default = ['css' => 'alert-info', 'icon' => 'o-information-circle'];
        return self::typedMessage(type: 'info', message: $message, title: $title)->options(
            options: [...$default, ...$options]
        );
    }

    public static function success(string $message, ?string $title = null, array $options = []): self
    {
        $default = ['css' => 'alert-success', 'icon' => 'o-check-circle'];
        return self::typedMessage(type: 'success', message: $message, title: $title)->options(
            options: [...$default, ...$options]
        );
    }

    public static function error(string $message, ?string $title = null, array $options = []): self
    {
        $default = ['css' => 'alert-error', 'icon' => 'o-x-circle'];
        return self::typedMessage(type: 'error', message: $message, title: $title)->options(
            options: [...$default, ...$options]
        );
    }

    public static function warning(string $message, ?string $title = null, array $options = []): self
    {
        $default = ['css' => 'alert-warning', 'icon' => 'o-exclamation-triangle'];
        return self::typedMessage(type: 'warning', message: $message, title: $title)->options(
            options: [...$default, ...$options]
        );
    }

    public function options(array $options): self
    {
        $this->position = $options['position'] ?? $this->position;
        $this->icon = $options['icon'] ?? $this->icon;
        $this->css = $options['css'] ?? $this->css;
        $this->timeout = $options['timeout'] ?? $this->timeout;
        $this->preventDefault = $options['preventDefault'] ?? $this->preventDefault;
        return $this;
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
                    'description' => $this->getMessage(),

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
