<?php

namespace Mary\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Str;

trait WithMediaSync
{
    // Remove media
    public function removeMedia(string $uuid, string $filesModelName, string $library, string $url): void
    {
        // Updates library
        $this->{$library} = $this->{$library}->filter(fn($image) => $image['uuid'] != $uuid);

        // Remove file
        $name = str($url)->after('preview-file/')->before('?expires')->toString();
        $this->{$filesModelName} = collect($this->{$filesModelName})->filter(fn($file) => $file->getFilename() != $name)->all();
    }

    // Set order
    public function refreshMediaOrder(array $order, string $library): void
    {
        $this->{$library} = $this->{$library}->sortBy(function ($item) use ($order) {
            return array_search($item['uuid'], $order);
        });
    }

    // Bind temporary files with respective previews and replace existing ones, if necessary
    public function refreshMediaSources(string $filesModelName, string $library)
    {
        // New files area
        foreach ($this->{$filesModelName}['*'] ?? [] as $key => $file) {
            $this->{$library} = $this->{$library}->add(['uuid' => Str::uuid()->toString(), 'url' => $file->temporaryUrl()]);

            $key = $this->{$library}->keys()->last();
            $this->{$filesModelName}[$key] = $file;
        }

        // Reset new files area
        unset($this->{$filesModelName}['*']);

        //Replace existing files
        foreach ($this->{$filesModelName} as $key => $file) {
            $media = $this->{$library}->get($key);
            $media['url'] = $file->temporaryUrl();

            $this->{$library} = $this->{$library}->replace([$key => $media]);
        }

        $this->validateOnly($filesModelName . '.*');
    }

    // Storage files into permanent area and updates the model with fresh sources
    public function syncMedia(
        Model $model,
        string $library = 'library',
        string $files = 'files',
        string $storage_subpath = '',
        $model_field = 'library',
        string $visibility = 'public',
        string $disk = 'public'
    ): void {
        // Store files
        foreach ($this->{$files} as $index => $file) {
            $media = $this->{$library}->get($index);
            $name = $this->getFileName($media);

            $file = Storage::disk($disk)->putFileAs($storage_subpath, $file, $name, $visibility);
            $url = Storage::disk($disk)->url($file);

            // Update library
            $media['url'] = $url . "?updated_at=" . time();
            $media['path'] = str($storage_subpath)->finish('/')->append($name)->toString();
            $this->{$library} = $this->{$library}->replace([$index => $media]);
        }

        // Delete removed files from library
        $diffs = $model->{$model_field}?->filter(fn($item) => $this->{$library}->doesntContain('uuid', $item['uuid'])) ?? [];

        foreach ($diffs as $diff) {
            Storage::disk($disk)->delete($diff['path']);
        }

        // Updates model
        $model->update([$model_field => $this->{$library}]);

        // Resets files
        $this->{$files} = [];
    }

    private function getFileName(?array $media): ?string
    {
        $name = $media['uuid'] ?? null;
        $extension = str($media['url'] ?? null)->afterLast('.')->before('?expires')->toString();

        return "$name.$extension";
    }
}
