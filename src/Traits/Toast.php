<?php

namespace Mary\Traits;

trait Toast
{
    public function success(
        string $title,
        string $description = null,
        string $position = 'top-10 right-10',
        int $timeout = 3000,
        string $redirectTo = null
    ) {
        return $this->toast('success', $title, $description, $position, $timeout, $redirectTo);
    }

    public function warning(
        string $title,
        string $description = null,
        string $position = 'top-10 right-10',
        int $timeout = 3000,
        string $redirectTo = null
    ) {
        return $this->toast('warning', $title, $description, $position, $timeout, $redirectTo);
    }

    public function error(
        string $title,
        string $description = null,
        string $position = 'top-10 right-10',
        int $timeout = 3000,
        string $redirectTo = null
    ) {
        return $this->toast('error', $title, $description, $position, $timeout, $redirectTo);
    }

    public function info(
        string $title,
        string $description = null,
        string $position = 'top-10 right-10',
        int $timeout = 3000,
        string $redirectTo = null
    ) {
        return $this->toast('info', $title, $description, $position, $timeout, $redirectTo);
    }

    public function toast(
        string $type,
        string $title,
        string $description = null,
        string $position = 'top-10 right-10',
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

        // Flash strategy if it has redirect url
        if ($redirectTo) {
            session()->flash('mary-flash', ['toast' => $toast]);

            return $this->redirect($redirectTo, navigate: true);
        }

        // Event strategy if it has not redirect url
        $this->dispatch('mary-toast', toast: $toast);
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
}
