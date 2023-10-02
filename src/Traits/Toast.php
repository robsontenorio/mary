<?php

namespace Mary\Traits;

trait Toast
{
    public function success(
        string $title,
        string $description = null,
        string $position = 'toast-top toast-end',
        int $timeout = 3000,
        string $redirectTo = null
    ) {
        return $this->toast('success', $title, $description, $position, $timeout, $redirectTo);
    }

    public function toast(
        string $type,
        string $title,
        string $description = null,
        string $position = 'toast-top toast-end',
        int $timeout = 3000,
        string $redirectTo = null
    ) {
        $toast = [
            'css' => $this->config($type)['css'] ?? '',
            'icon' => $this->config($type)['icon'] ?? 'o-alert-info',
            'title' => $title,
            'description' => $description,
            'position' => $position,
            'timeout' => $timeout,
        ];

        $this->js('toast(' . json_encode(['toast' => $toast]) . ')');

        session()->flash('mary.toast.title', $title);
        session()->flash('mary.toast.description', $description);

        if ($redirectTo) {
            return $this->redirect($redirectTo, navigate: true);
        }
    }

    private function config($type): array
    {
        return [
            'success' => [
                'css' => 'alert-success',
                'icon' => 's-check-circle',
            ],
            'warning' => [
                'css' => 'alert-warning',
                'icon' => 's-exclamation-triangle',
            ],
            'info' => [
                'css' => 'alert-info',
                'icon' => 's-information-circle',
            ],
            'error' => [
                'css' => 'alert-error',
                'icon' => 's-x-circle',
            ],
        ][$type] ?? [];
    }

    public function warning(
        string $title,
        string $description = null,
        string $position = 'toast-top toast-end',
        int $timeout = 3000,
        string $redirectTo = null
    ) {
        return $this->toast('warning', $title, $description, $position, $timeout, $redirectTo);
    }

    public function error(
        string $title,
        string $description = null,
        string $position = 'toast-top toast-end',
        int $timeout = 3000,
        string $redirectTo = null
    ) {
        return $this->toast('error', $title, $description, $position, $timeout, $redirectTo);
    }

    public function info(
        string $title,
        string $description = null,
        string $position = 'toast-top toast-end',
        int $timeout = 3000,
        string $redirectTo = null
    ) {
        return $this->toast('info', $title, $description, $position, $timeout, $redirectTo);
    }
}
