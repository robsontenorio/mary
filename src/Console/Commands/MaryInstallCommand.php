<?php

namespace Mary\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Str;
use RuntimeException;
use function Laravel\Prompts\select;

class MaryInstallCommand extends Command
{
    protected $signature = 'mary:install';

    protected $description = 'Command description';

    protected $ds = DIRECTORY_SEPARATOR;

    public function handle()
    {
        $this->info("❤️ maryUI installer");

        // Install Volt ?
        $shouldInstallVolt = $this->askForVolt();

        //Yarn or Npm ?
        $packageManagerCommand = $this->askForPackageInstaller();

        // Install Livewire/Volt
        $this->installLivewire($shouldInstallVolt);

        // Setup Tailwind and Daisy
        $this->setupTailwindDaisy($packageManagerCommand);

        // Copy stubs if is brand-new project
        $this->copyStubs();

        // Rename components if Jetstream or Breeze are detected
        $this->renameComponents();

        $this->info("\n✅ Done! Run `yarn dev or npm run dev`");
        $this->info("🌟 Give it a star: https://github.com/robsontenorio/mary");
        $this->info("❤️ Sponsor this project: https://github.com/sponsors/robsontenorio\n");
    }

    public function installLivewire(string $shouldInstallVolt)
    {
        $this->info("\nInstalling Livewire...\n");

        $extra = $shouldInstallVolt == 'Yes'
            ? ' livewire/volt && php artisan volt:install'
            : '';

        Process::run("composer require livewire/livewire $extra", function (string $type, string $output) {
            echo $output;
        })->throw();
    }

    public function setupTailwindDaisy(string $packageManagerCommand)
    {
        /**
         * Install daisyUI + Tailwind
         */
        $this->info("\nInstalling daisyUI + Tailwind...\n");

        Process::run("$packageManagerCommand tailwindcss daisyui@latest postcss autoprefixer && npx tailwindcss init -p", function (string $type, string $output) {
            echo $output;
        })->throw();

        /**
         * Setup app.css
         */

        $cssPath = base_path() . "{$this->ds}resources{$this->ds}css{$this->ds}app.css";
        $css = File::get($cssPath);

        if (! str($css)->contains('@tailwind')) {
            $stub = File::get(__DIR__ . "/../../../stubs/app.css");
            File::put($cssPath, str($css)->prepend($stub));
        }

        /**
         * Setup tailwind.config.js
         */

        $tailwindJsPath = base_path() . "{$this->ds}tailwind.config.js";

        if (! File::exists($tailwindJsPath)) {
            $this->copyFile(__DIR__ . "/../../../stubs/tailwind.config.js", "tailwind.config.js");

            return;
        }

        /**
         * Setup Tailwind plugins
         */

        $tailwindJs = File::get($tailwindJsPath);
        $originalPlugins = str($tailwindJs)->after('plugins')->after('[')->before(']');

        if ($originalPlugins->contains('daisyui')) {
            return;
        }

        $plugins = str($tailwindJs)->replace('plugins: []', 'plugins: [require("daisyui")]');

        if (! $originalPlugins->isEmpty()) {
            $plugins = $originalPlugins->squish()->trim()->remove(' ')->explode(',')->add('require("daisyui")')->filter()->implode(',');
            $plugins = str($plugins)->prepend("\n\t\t")->replace(',', ",\n\t\t")->append("\r\n\t");
            $plugins = str($tailwindJs)->replace($originalPlugins, $plugins);
        }

        File::put($tailwindJsPath, $plugins);

        /**
         * Setup Tailwind contents
         */
        $tailwindJs = File::get($tailwindJsPath);
        $originalContents = str($tailwindJs)->after('contents')->after('[')->before(']');

        if ($originalContents->contains('robsontenorio/mary')) {
            return;
        }

        $contents = $originalContents->squish()->trim()->remove(' ')->explode(',')->add('"./vendor/robsontenorio/mary/src/View/Components/**/*.php"')->filter()->implode(', ');
        $contents = str($contents)->prepend("\n\t\t")->replace(',', ",\n\t\t")->append("\r\n\t");
        $contents = str($tailwindJs)->replace($originalContents, $contents);

        File::put($tailwindJsPath, $contents);
    }

    /**
     * If Jetstream or Breeze are detected we publish config file and add a global prefix to maryUI components,
     * in order to avoid name collision with existing components.
     */
    public function renameComponents()
    {
        $composerJson = File::get(base_path() . "/composer.json");

        collect(['jetstream', 'breeze'])->each(function (string $target) use ($composerJson) {
            if (str($composerJson)->contains($target)) {
                Artisan::call('vendor:publish --tag mary.config');

                $path = base_path() . "{$this->ds}config{$this->ds}mary.php";
                $config = File::get($path);
                $contents = str($config)->replace("'prefix' => ''", "'prefix' => 'mary-'");
                File::put($path, $contents);

                $this->warn("\n\n🚨`$target` was detected.🚨");
                $this->warn("A global prefix on maryUI components was added to avoid name collision.");
                $this->warn("\n * Example: x-mary-button, x-mary-card ...");
                $this->warn(" * See config/mary.php\n");
            }
        });
    }

    /**
     * Copy example demo stub if it is a brand-new project.
     */
    public function copyStubs(): void
    {
        // If there is something, skip stubs
        $web = base_path() . "{$this->ds}routes{$this->ds}web.php";

        if (count(file($web)) > 20) {
            return;
        }

        $this->info("Copying stubs...\n");

        $appViewComponents = "app{$this->ds}View{$this->ds}Components";
        $livewirePath = "app{$this->ds}Livewire";
        $layoutsPath = "resources{$this->ds}views{$this->ds}components{$this->ds}layouts";
        $livewireBladePath = "resources{$this->ds}views{$this->ds}livewire";

        $this->createDirectoryIfNotExists($appViewComponents);
        $this->createDirectoryIfNotExists($livewirePath);
        $this->createDirectoryIfNotExists($livewireBladePath);
        $this->createDirectoryIfNotExists($layoutsPath);

        $this->copyFile(__DIR__ . "/../../../stubs/AppBrand.php", "{$appViewComponents}{$this->ds}AppBrand.php");
        $this->copyFile(__DIR__ . "/../../../stubs/app.blade.php", "{$layoutsPath}{$this->ds}app.blade.php");

        $this->copyFile(__DIR__ . "/../../../stubs/Welcome.php", "{$livewirePath}{$this->ds}Welcome.php");
        $this->copyFile(__DIR__ . "/../../../stubs/welcome.blade.php", "{$livewireBladePath}{$this->ds}welcome.blade.php");

        $this->copyFile(__DIR__ . "/../../../stubs/web.php", $web);
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
