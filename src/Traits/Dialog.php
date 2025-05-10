<?php

namespace Mary\Traits;

use Blade;

trait Dialog
{
    /**
     * Show a dialog with common parameters
     *
     * @param string $css
     * @param string $title
     * @param string|null $description
     * @param string|null $position
     * @param array|null $confirmOptions
     * @param array|null $cancelOptions
     * @param array $backdrop
     * @return void
     */
    public function dialog(
        ?string $title = null,
        ?string $description = null,
        ?string $position = 'center',
        ?array $confirmOptions = null,
        ?array $cancelOptions = null,
        ?string $icon = 'o-information-circle',
        string $css = '',
        bool $backdrop = true,
        bool $blur = false,
    ): void {
        $dialog = [
            'title' => $title,
            'description' => $description,
            'icon' => $icon ? Blade::render("<x-mary-icon class='w-7 h-7' name='".$icon."' />") : null,
            'css' => $css,
            'position' => $position,
            'backdrop' => $backdrop,
            'blur' => $blur,
            'confirmOptions' => $confirmOptions,
            'cancelOptions' => $cancelOptions
        ];

        $this->js('dialog('.json_encode(['dialog' => $dialog]).')');

        session()->flash('mary.dialog.title', $title);
        session()->flash('mary.dialog.description', $description);
    }

    /**
     * Show a dialog with a specific type
     *
     * @param string $type
     * @param string|null $title
     * @param string|null $description
     * @param string|null $position
     * @param array|null $confirmOptions
     * @param array|null $cancelOptions
     * @return void
     */
    public function dialogWithType(
        string $type,
        ?string $title = null,
        ?string $description = null,
        ?string $position = null,
        ?array $confirmOptions = ['text' => 'Ok'],
        ?array $cancelOptions = null,
        ?string $icon = null,
    ): void {
        $defaultIcons = [
            'success' => 'o-check-circle',
            'error' => 'o-x-circle',
            'info' => 'o-information-circle',
            'warning' => 'o-exclamation-triangle',
            'confirm' => 'o-question-mark-circle',
        ];

        $this->dialog(
            $title,
            $description,
            $position,
            $confirmOptions,
            $cancelOptions,
            $icon ?? $defaultIcons[$type] ?? 'o-information-circle',
            "dialog-$type"
        );
    }
}
