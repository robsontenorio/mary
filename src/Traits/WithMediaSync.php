<?php

namespace Mary\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Str;

trait WithMediaSync
{
    // Remove media
    public function removeMedia(string $uuid, string $filesModelName, string $library, string $path): void
    {
        // Updates library
        $this->{$library} = $this->{$library}->filter(fn($image) => $image['uuid'] != $uuid);

        // Remove file
        $name = str($path)->after('preview-file/')->before('?expires')->toString();
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
            $this->{$library} = $this->{$library}->add(['uuid' => Str::uuid()->toString(), 'path' => $file->temporaryUrl()]);

            $key = $this->{$library}->keys()->last();
            $this->{$filesModelName}[$key] = $file;
        }

        // Reset new files area
        unset($this->{$filesModelName}['*']);

        //Replace existing files
        foreach ($this->{$filesModelName} as $key => $file) {
            $this->{$library} = $this->{$library}->replace([
                $key => ['uuid' => Str::uuid()->toString(), 'path' => $file->temporaryUrl()]
            ]);
        }

        $this->validateOnly($filesModelName . '.*');
    }

    // Storage files into permanent area and updates the model with fresh sources
    public function syncMedia(
        Model $model,
        mixed $files,
        Collection $library,
        string $storage_subpath = '',
        $model_field = 'library',
        string $visibility = 'public',
        string $disk = 'public'
    ): void {
        $uploads = collect();

        // Storage files
        foreach ($files as $file) {
            $tmp = Storage::disk($disk)->putFile($storage_subpath, $file, $visibility);
            $url = Storage::disk($disk)->url($tmp);

            $uploads->add(['url' => $url, 'filename' => $file->getFilename()]);
        }

        // Replace temporary sources for permanent sources
        $images = $library
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
