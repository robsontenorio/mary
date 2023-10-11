<?php

namespace Mary\Console\Commands;

use Arr;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Str;

class MaryInstallCommand extends Command
{
    protected $signature = 'mary:install';

    protected $description = 'Command description';

    public function handle()
    {
        $this->info("🔨 Installing Mary...\n");

        /**
         * Check for starter kits
         */
        $composerJson = File::get(base_path() . "/composer.json");
        $targets = ['breeze', 'jetstream', 'genesis'];

        if (Str::of($composerJson)->contains($targets)) {
            $this->error("Automatic install works only for brand-new Laravel projects.");
            $this->warn("Detected one of these: " . Arr::join($targets, ', ', ' or '));

            return;
        }

        /**
         * Check for JS packages
         */
        $packageJson = File::get(base_path() . "/package.json");
        $targets = ['tailwindcss', 'daisyui'];

        if (Str::of($packageJson)->contains($targets)) {
            $this->error("Automatic install works only for brand-new Laravel projects.");
            $this->warn("Detected one of these: " . Arr::join($targets, ', ', ' or '));

            return;
        }

        /**
         * Check for all stubs
         */
        collect([
            [
                'path' => 'resources/views/components/layouts',
                'name' => 'app.blade.php'
            ],
            [
                'path' => 'resources/css/',
                'name' => 'app.css'
            ],
            [
                'path' => 'app/Livewire/',
                'name' => 'Welcome.php',
            ],
            [
                'path' => '',
                'name' => 'tailwind.config.js',
            ]
        ])->each(function (array $item) {
            $file = base_path() . '/' . $item['path'] . $item['name'];

            if (File::exists($file)) {
                $this->error("Automatic install works only for brand-new Laravel projects.");
                $this->warn('Detected:' . $file);

                exit;
            }
        });

        /**
         * Install Livewire
         */
        $this->info("\nInstalling Livewire...\n");

        Process::run('composer require livewire/livewire', function (string $type, string $output) {
            echo $output;
        })->throw();

        /**
         * Install daisyUI + Tailwind
         */
        $this->info("\nInstalling daisyUI + Tailwind...\n");

        Process::run('yarn add -D tailwindcss daisyui@latest postcss autoprefixer && npx tailwindcss init -p', function (string $type, string $output) {
            echo $output;
        })->throw();

        /**
         * Copy all stubs
         */
        $this->info("Copying stubs...\n");

        Process::run('mkdir -p app/Livewire && mkdir -p resources/views/components/layouts', function (string $type, string $output) {
            echo $output;
        })->throw();

        Process::run('cp ' . __DIR__ . '/../../../stubs/app.blade.php resources/views/components/layouts/', function (string $type, string $output) {
            echo $output;
        })->throw();

        Process::run('cp ' . __DIR__ . '/../../../stubs/app.css resources/css/', function (string $type, string $output) {
            echo $output;
        })->throw();

        Process::run('cp ' . __DIR__ . '/../../../stubs/tailwind.config.js .', function (string $type, string $output) {
            echo $output;
        })->throw();

        Process::run('cp ' . __DIR__ . '/../../../stubs/Welcome.php app/Livewire/', function (string $type, string $output) {
            echo $output;
        })->throw();

        Process::run('cp ' . __DIR__ . '/../../../stubs/web.php routes/', function (string $type, string $output) {
            echo $output;
        })->throw();

        $this->info("\n🌟 Done! Run `yarn dev or npm run dev`\n");
    }
}
