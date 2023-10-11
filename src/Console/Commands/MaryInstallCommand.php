<?php

namespace Mary\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Str;
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
    }

    public function askForPackageInstaller(): string
    {
        $yarn = Process::run('which yarn')->output();
        $npm = Process::run('which npm')->output();

        $options = [];

        if (Str::of($yarn)->isNotEmpty()) {
            $options = array_merge($options, ['yarn add -D' => 'yarn']);
        }

        if (Str::of($npm)->isNotEmpty()) {
            $options = array_merge($options, ['npm install --dev' => 'npm',]);
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
}
