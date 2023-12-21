<?php

namespace Mary\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Str;
use RuntimeException;
use function Laravel\Prompts\select;

class MaryInstallCommand extends Command
{
    protected $signature = 'mary:install';

    protected $description = 'Command description';

    public function handle()
    {
        $this->info("🔨 Mary installer");

        $this->warn('
It will set up:
- Livewire
- Tailwind + daisyUI
- Default layout
- Welcome component
- Route to Welcome');

        /**
         * Install Volt ?
         */
        $shouldInstallVolt = $this->askForVolt();

        /**
         * Yarn or Npm ?
         */
        $packageManagerCommand = $this->askForPackageInstaller();

        /**
         * Check for existing STARTER KIT packages
         */
        $composerJson = File::get(base_path() . "/composer.json");
        $targets = ['breeze', 'jetstream', 'genesis'];
        $this->checkForExistingPackages($composerJson, $targets);

        /**
         * Check for existing JS packages
         */
        $packageJson = File::get(base_path() . "/package.json");
        $targets = ['tailwindcss', 'daisyui'];
        $this->checkForExistingPackages($packageJson, $targets);

        /**
         * Check for stubs
         */
        $this->checkForStubs();

        /**
         * Install Livewire
         */
        $this->info("\nInstalling Livewire...\n");

        Process::run('composer require livewire/livewire', function (string $type, string $output) {
            echo $output;
        })->throw();

        if ($shouldInstallVolt == 'Yes') {
            $this->info("\nInstalling Livewire Volt...\n");

            Process::run('composer require livewire/volt', function (string $type, string $output) {
                echo $output;
            })->throw();

            Process::run('php artisan volt:install', function (string $type, string $output) {
                echo $output;
            })->throw();
        }

        /**
         * Install daisyUI + Tailwind
         */
        $this->info("\nInstalling daisyUI + Tailwind...\n");

        Process::run("$packageManagerCommand tailwindcss daisyui@latest postcss autoprefixer && npx tailwindcss init -p", function (string $type, string $output) {
            echo $output;
        })->throw();

        /**
         * Copy all stubs
         */
        $this->copyStubs();

        $this->info("\n🌟 Done! Run `yarn dev or npm run dev`\n");
    }

    /**
     * Check for existing packages
     */
    public function checkForExistingPackages(string $content, array $targets): void
    {
        collect($targets)->each(function (string $target) use ($content) {
            if (Str::of($content)->contains($target)) {
                $this->error("Automatic install works only for brand-new Laravel projects.");
                $this->warn("Detected: " . $target);

                exit;
            }
        });
    }

    /**
     * Check for existing stubs
     */
    public function checkForStubs(): void
    {
        collect([
            [
                'path' => 'resources/views/components/layouts',
                'name' => 'app.blade.php'
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
    }

    public function copyStubs(): void
    {
        $this->info("Copying stubs...\n");

        $ds = DIRECTORY_SEPARATOR;

        $appViewComponents = "app{$ds}View{$ds}Components";
        $livewirePath = "app{$ds}Livewire";
        $layoutsPath = "resources{$ds}views{$ds}components{$ds}layouts";
        $livewireBladePath = "resources{$ds}views{$ds}livewire";
        $cssPath = "resources{$ds}css";
        $routesPath = "routes";

        $this->createDirectoryIfNotExists($appViewComponents);
        $this->createDirectoryIfNotExists($livewirePath);
        $this->createDirectoryIfNotExists($livewireBladePath);
        $this->createDirectoryIfNotExists($layoutsPath);

        $this->copyFile(__DIR__ . "/../../../stubs/AppBrand.php", "{$appViewComponents}{$ds}AppBrand.php");
        $this->copyFile(__DIR__ . "/../../../stubs/app.blade.php", "{$layoutsPath}{$ds}app.blade.php");
        $this->copyFile(__DIR__ . "/../../../stubs/app.css", "{$cssPath}{$ds}app.css");
        $this->copyFile(__DIR__ . "/../../../stubs/tailwind.config.js", "tailwind.config.js");
        $this->copyFile(__DIR__ . "/../../../stubs/Welcome.php", "{$livewirePath}{$ds}Welcome.php");
        $this->copyFile(__DIR__ . "/../../../stubs/welcome.blade.php", "{$livewireBladePath}{$ds}welcome.blade.php");
        $this->copyFile(__DIR__ . "/../../../stubs/web.php", "{$routesPath}{$ds}web.php");
    }

    public function askForPackageInstaller(): string
    {
        $os = PHP_OS;
        $findCommand = stripos($os, 'WIN') === 0 ? 'where' : 'which';

        $yarn = Process::run($findCommand . ' yarn')->output();
        $npm = Process::run($findCommand . ' npm')->output();

        $options = [];

        if (Str::of($yarn)->isNotEmpty()) {
            $options = array_merge($options, ['yarn add -D' => 'yarn']);
        }

        if (Str::of($npm)->isNotEmpty()) {
            $options = array_merge($options, ['npm install --save-dev' => 'npm']);
        }

        if (count($options) == 0) {
            $this->error("You need yarn or npm installed.");

            exit;
        }

        return select(
            label: 'Install with ...',
            options: $options
        );
    }

    /**
     * Also install Volt?
     */
    public function askForVolt(): string
    {
        return select(
            'Also install `livewire/volt` ?',
            ['Yes', 'No'],
            hint: 'No matter what is your choice, it always installs `livewire/livewire`'
        );
    }

    private function createDirectoryIfNotExists(string $path): void
    {
        if (! file_exists($path)) {
            mkdir($path, 0777, true);
        }
    }

    private function copyFile(string $source, string $destination): void
    {
        $source = str_replace('/', DIRECTORY_SEPARATOR, $source);
        $destination = str_replace('/', DIRECTORY_SEPARATOR, $destination);

        if (! copy($source, $destination)) {
            throw new RuntimeException("Failed to copy {$source} to {$destination}");
        }
    }
}
