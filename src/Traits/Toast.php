<?php

namespace Mary\Traits;

use Blade;

trait Toast
{
    public function toast(
        string $type,
        string $title,
        string $description = null,
        string $position = null,
        string $icon = 'o-information-circle',
        string $css = 'alert-info',
        int $timeout = 3000,
        string $redirectTo = null
    ) {
        $toast = [
            'type' => $type,
            'title' => $title,
            'description' => $description,
            'position' => $position,
            'icon' => Blade::render("<x-mary-icon class='w-7 h-7' name='" . $icon . "' />"),
            'css' => $css,
            'timeout' => $timeout,
        ];

        $this->js('toast(' . json_encode(['toast' => $toast]) . ')');

        session()->flash('mary.toast.title', $title);
        session()->flash('mary.toast.description', $description);

        if ($redirectTo) {
            return $this->redirect($redirectTo, navigate: true);
        }
    }

    public function success(
        string $title,
        string $description = null,
        string $position = null,
        string $icon = 'o-check-circle',
        string $css = 'alert-success',
        int $timeout = 3000,
        string $redirectTo = null
    ) {
        return $this->toast('success', $title, $description, $position, $icon, $css, $timeout, $redirectTo);
    }

    public function warning(
        string $title,
        string $description = null,
        string $position = null,
        string $icon = 'o-exclamation-triangle',
        string $css = 'alert-warning',
        int $timeout = 3000,
        string $redirectTo = null
    ) {
        return $this->toast('warning', $title, $description, $position, $icon, $css, $timeout, $redirectTo);
    }

    public function error(
        string $title,
        string $description = null,
        string $position = null,
        string $icon = 'o-x-circle',
        string $css = 'alert-error',
        int $timeout = 3000,
        string $redirectTo = null
    ) {
        return $this->toast('error', $title, $description, $position, $icon, $css, $timeout, $redirectTo);
    }

    public function info(
        string $title,
        string $description = null,
        string $position = null,
        string $icon = 'o-information-circle',
        string $css = 'alert-info',
        int $timeout = 3000,
        string $redirectTo = null
    ) {
        return $this->toast('info', $title, $description, $position, $icon, $css, $timeout, $redirectTo);
    }
}
