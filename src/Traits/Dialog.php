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
     * Show a success dialog
     *
     * @param string $title
     * @param string|null $description
     * @param string|null $position
     * @param array|null $confirmOptions
     * @param array|null $cancelOptions
     * @return void
     */
    public function dialogSuccess(
        ?string $title = null,
        ?string $description = null,
        ?string $position = null,
        ?array $confirmOptions = ['text' => 'Ok'],
        ?array $cancelOptions = null,
        ?string $icon = 'o-check-circle',
    ): void {
        $this->dialog($title, $description, $position, $confirmOptions, $cancelOptions, $icon, 'dialog-success');
    }

    /**
     * Show an error dialog
     *
     * @param string $title
     * @param string|null $description
     * @param string|null $position
     * @param array|null $confirmOptions
     * @param array|null $cancelOptions
     * @return void
     */
    public function dialogError(
        ?string $title = null,
        ?string $description = null,
        ?string $position = null,
        ?array $confirmOptions = ['text' => 'Ok'],
        ?array $cancelOptions = null,
        ?string $icon = 'o-x-circle',
    ): void {
        $this->dialog($title, $description, $position, $confirmOptions, $cancelOptions, $icon, 'dialog-error');
    }

    /**
     * Show an info dialog
     *
     * @param string $title
     * @param string|null $description
     * @param string|null $position
     * @param array|null $confirmOptions
     * @param array|null $cancelOptions
     * @return void
     */
    public function dialogInfo(
        ?string $title = null,
        ?string $description = null,
        ?string $position = null,
        ?array $confirmOptions = ['text' => 'Ok'],
        ?array $cancelOptions = null,
        ?string $icon = 'o-information-circle',
    ): void {
        $this->dialog($title, $description, $position, $confirmOptions, $cancelOptions, $icon, 'dialog-info');
    }

    /**
     * Show a warning dialog
     *
     * @param string $title
     * @param string|null $description
     * @param string|null $position
     * @param array|null $confirmOptions
     * @param array|null $cancelOptions
     * @return void
     */
    public function dialogWarning(
        ?string $title = null,
        ?string $description = null,
        ?string $position = null,
        ?array $confirmOptions = ['text' => 'Ok'],
        ?array $cancelOptions = null,
        ?string $icon = 'o-exclamation-triangle',
    ): void {
        $this->dialog($title, $description, $position, $confirmOptions, $cancelOptions, $icon, 'dialog-warning');
    }

    /**
     * Show a confirmation dialog with confirm and cancel options
     *
     * @param string $title
     * @param string|null $description
     * @param string|null $position
     * @param array|null $confirmOptions
     * @param array|null $cancelOptions
     * @return void
     */
    public function dialogConfirm(
        ?string $title = null,
        ?string $description = null,
        ?string $position = null,
        ?array $confirmOptions = ['text' => 'Ok'],
        ?array $cancelOptions = ['text' => 'Cancel'],
        ?string $icon = 'o-question-mark-circle',
    ): void {
        $this->dialog($title, $description, $position, $confirmOptions, $cancelOptions, $icon, 'dialog-confirm');
    }
}
