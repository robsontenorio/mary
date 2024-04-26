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
        $this->info("❤️  maryUI installer");

        // Install Volt ?
        $shouldInstallVolt = $this->askForVolt();

        //Yarn or Npm or Bun or Pnpm ?
        $packageManagerCommand = $this->askForPackageInstaller();

        // Install Livewire/Volt
        $this->installLivewire($shouldInstallVolt);

        // Setup Tailwind and Daisy
        $this->setupTailwindDaisy($packageManagerCommand);

        // Copy stubs if is brand-new project
        $this->copyStubs($shouldInstallVolt);

        // Rename components if Jetstream or Breeze are detected
        $this->renameComponents();

        // Clear view cache
        Artisan::call('view:clear');

        $this->info("\n");
        $this->info("✅  Done! Run `yarn dev` or `npm run dev` or `bun run dev` or `pnpm dev`");
        $this->info("🌟  Give it a star: https://github.com/robsontenorio/mary");
        $this->info("❤️  Sponsor this project: https://github.com/sponsors/robsontenorio\n");
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

        Process::run("$packageManagerCommand tailwindcss daisyui@latest postcss autoprefixer", function (string $type, string $output) {
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
            $this->copyFile(__DIR__ . "/../../../stubs/postcss.config.js", "postcss.config.js");

            return;
        }

        /**
         * Setup Tailwind plugins
         */

        $tailwindJs = File::get($tailwindJsPath);
        $pluginsBlock = str($tailwindJs)->match('/plugins:[\S\s]*\[[\S\s]*\]/');

        if ($pluginsBlock->contains('daisyui')) {
            return;
        }

        $plugins = $pluginsBlock->after('plugins')->after('[')->before(']')->squish()->trim()->remove(' ')->explode(',')->add('require("daisyui")')->filter()->implode(',');
        $plugins = str($plugins)->prepend("\n\t\t")->replace(',', ",\n\t\t")->append("\r\n\t");
        $plugins = str($tailwindJs)->replace($pluginsBlock, "plugins: [$plugins]");

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
                Artisan::call('vendor:publish --force --tag mary.config');

                $path = base_path() . "{$this->ds}config{$this->ds}mary.php";
                $config = File::get($path);
                $contents = str($config)->replace("'prefix' => ''", "'prefix' => 'mary-'");
                File::put($path, $contents);

                $this->warn('---------------------------------------------');
                $this->warn("🚨`$target` was detected.🚨");
                $this->warn('---------------------------------------------');
                $this->warn("A global prefix on maryUI components was added to avoid name collision.");
                $this->warn("\n * Example: x-mary-button, x-mary-card ...");
                $this->warn(" * See config/mary.php");
                $this->warn('---------------------------------------------');
            }
        });
    }

    /**
     * Copy example demo stub if it is a brand-new project.
     */
    public function copyStubs(string $shouldInstallVolt): void
    {
        $composerJson = File::get(base_path() . "/composer.json");
        $hasKit = str($composerJson)->contains('jetstream') || str($composerJson)->contains('breeze');

        if ($hasKit) {
            $this->warn('---------------------------------------------');
            $this->warn('🚨 Starter kit detected. Skipping demo components. 🚨');
            $this->warn('---------------------------------------------');
            return;
        }

        $this->info("Copying stubs...\n");

        $routes = base_path() . "{$this->ds}routes";
        $appViewComponents = "app{$this->ds}View{$this->ds}Components";
        $livewirePath = "app{$this->ds}Livewire";
        $layoutsPath = "resources{$this->ds}views{$this->ds}components{$this->ds}layouts";
        $livewireBladePath = "resources{$this->ds}views{$this->ds}livewire";

        // Blade Brand component
        $this->createDirectoryIfNotExists($appViewComponents);
        $this->copyFile(__DIR__ . "/../../../stubs/AppBrand.php", "{$appViewComponents}{$this->ds}AppBrand.php");

        // Default app layout
        $this->createDirectoryIfNotExists($layoutsPath);
        $this->copyFile(__DIR__ . "/../../../stubs/app.blade.php", "{$layoutsPath}{$this->ds}app.blade.php");

        // Livewire blade views
        $this->createDirectoryIfNotExists($livewireBladePath);

        // Demo component and its route
        if ($shouldInstallVolt == 'Yes') {
            $this->createDirectoryIfNotExists("$livewireBladePath{$this->ds}users");
            $this->copyFile(__DIR__ . "/../../../stubs/index.blade.php", "$livewireBladePath{$this->ds}users{$this->ds}index.blade.php");
            $this->copyFile(__DIR__ . "/../../../stubs/web-volt.php", "$routes{$this->ds}web.php");
        } else {
            $this->createDirectoryIfNotExists($livewirePath);
            $this->copyFile(__DIR__ . "/../../../stubs/Welcome.php", "{$livewirePath}{$this->ds}Welcome.php");
            $this->copyFile(__DIR__ . "/../../../stubs/welcome.blade.php", "{$livewireBladePath}{$this->ds}welcome.blade.php");
            $this->copyFile(__DIR__ . "/../../../stubs/web.php", "$routes{$this->ds}web.php");
        }
    }

    public function askForPackageInstaller(): string
    {
        $os = PHP_OS;
        $findCommand = stripos($os, 'WIN') === 0 ? 'where' : 'which';

        $yarn = Process::run($findCommand . ' yarn')->output();
        $npm = Process::run($findCommand . ' npm')->output();
        $bun = Process::run($findCommand . ' bun')->output();
        $pnpm = Process::run($findCommand . ' pnpm')->output();

        $options = [];

        if (Str::of($yarn)->isNotEmpty()) {
            $options = array_merge($options, ['yarn add -D' => 'yarn']);
        }

        if (Str::of($npm)->isNotEmpty()) {
            $options = array_merge($options, ['npm install --save-dev' => 'npm']);
        }

        if (Str::of($bun)->isNotEmpty()) {
            $options = array_merge($options, ['bun i -D' => 'bun']);
        }

        if (Str::of($pnpm)->isNotEmpty()) {
            $options = array_merge($options, ['pnpm i -D' => 'pnpm']);
        }

        if (count($options) == 0) {
            $this->error("You need yarn or npm or bun or pnpm installed.");

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
