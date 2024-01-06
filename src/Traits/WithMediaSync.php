<?php

namespace Mary\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Str;

trait WithMediaSync
{
    // Remove media
    public function removeMedia(string $uuid, string $filesModelName, string $preview, string $path): void
    {
        // Updates preview
        $this->{$preview} = $this->{$preview}->filter(fn($image) => $image['uuid'] != $uuid);

        // Remove file
        $name = str($path)->after('preview-file/')->before('?expires')->toString();
        $this->{$filesModelName} = collect($this->{$filesModelName})->filter(fn($file) => $file->getFilename() != $name)->all();
    }

    // Set order
    public function refreshMediaOrder(array $order, string $preview): void
    {
        $this->{$preview} = $this->{$preview}->sortBy(function ($item) use ($order) {
            return array_search($item['uuid'], $order);
        });
    }

    // Bind temporary files with respective previews and replace existing ones, if necessary
    public function refreshMediaSources(string $filesModelName, string $previewsName)
    {
        // New files area
        foreach ($this->{$filesModelName}['*'] ?? [] as $key => $file) {
            $this->{$previewsName} = $this->{$previewsName}->add(['uuid' => Str::uuid()->toString(), 'path' => $file->temporaryUrl()]);

            $key = $this->{$previewsName}->keys()->last();
            $this->{$filesModelName}[$key] = $file;
        }

        // Reset new files area
        unset($this->{$filesModelName}['*']);

        //Replace existing files
        foreach ($this->{$filesModelName} as $key => $file) {
            $this->{$previewsName} = $this->{$previewsName}->replace([
                $key => ['uuid' => Str::uuid()->toString(), 'path' => $file->temporaryUrl()]
            ]);
        }
    }

    // Storage files into permanent area and updates the model with fresh sources
    public function syncMedia(Model $model, mixed $files, Collection $previews, string $storage_subpath = '', $model_field = 'library', string $visibility = 'public'): void
    {
        $uploads = collect();

        // Storage files
        foreach ($files as $file) {
            $uploads->add(['url' => "/storage/" . $file->store($storage_subpath, $visibility), 'filename' => $file->getFilename()]);
        }

        // Replace temporary sources for permanent sources
        $images = $previews
            ->map(function ($item) use ($uploads) {
                $upload = $uploads->filter(function ($upload) use ($item) {
                    return str($item['path'])->contains($upload['filename']);
                })->first();

                if ($upload) {
                    $item['path'] = $upload['url'];
                }

                return $item;
            });

        // Updates model
        $model->update([$model_field => $images]);
    }
}
