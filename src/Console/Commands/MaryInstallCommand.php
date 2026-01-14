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

    public function handle(): void
    {
        $this->info("❤️  maryUI installer");

        // Laravel 12+
        $this->checkForLaravelVersion();

        //Yarn or Npm or Bun or Pnpm ?
        $packageManagerCommand = $this->askForPackageInstaller();

        // Install Livewire
        $this->installLivewire();

        // Setup Tailwind and Daisy
        $this->setupTailwindDaisy($packageManagerCommand);

        // Copy stubs if is brand-new project
        $this->copyStubs();

        // Rename components if Jetstream or Breeze are detected
        $this->renameComponents();

        // Clear view cache
        Artisan::call('view:clear');

        $this->info("\n");
        $this->info("✅  Done!");
        $this->info("❤️  Sponsor: https://github.com/sponsors/robsontenorio");
        $this->info("\n");
    }

    public function installLivewire(): void
    {
        $this->info("\nInstalling Livewire...\n");

        Process::run("composer require livewire/livewire", function (string $type, string $output) {
            echo $output;
        })->throw();
    }

    public function setupTailwindDaisy(string $packageManagerCommand): void
    {
        /**
         * Install daisyUI + Tailwind
         */
        $this->info("\nInstalling daisyUI + Tailwind...\n");

        Process::run("$packageManagerCommand daisyui tailwindcss @tailwindcss/vite", function (string $type, string $output) {
            echo $output;
        })->throw();

        /**
         * Setup app.css
         */
        $cssPath = base_path() . "{$this->ds}resources{$this->ds}css{$this->ds}app.css";
        $css = File::get($cssPath);

        $mary = <<<EOT
            \n\n
            /**
                The lines above are intact.
                The lines below were added by maryUI installer.
            */

            /** daisyUI */
            @plugin "daisyui" {
                themes: light --default, dark --prefersdark;
            }

            /* maryUI */
            @source "../../vendor/robsontenorio/mary/src/View/Components/**/*.php";

            /* Theme toggle */
            @custom-variant dark (&:where(.dark, .dark *));

            /**
            * Paginator - Traditional style
            * Because Laravel defaults does not match well the design of daisyUI.
            */

            .mary-table-pagination span[aria-current="page"] > span {
                @apply bg-primary text-base-100
            }

            .mary-table-pagination button {
                @apply cursor-pointer
            }
            EOT;

        $css = str($css)->append($mary);

        File::put($cssPath, $css);
    }

    /**
     * If Jetstream or Breeze are detected we publish config file and add a global prefix to maryUI components,
     * in order to avoid name collision with existing components.
     */
    public function renameComponents(): void
    {
        $composerJson = File::get(base_path() . "/composer.json");

        collect(['jetstream', 'breeze', 'livewire/flux'])->each(function (string $target) use ($composerJson) {
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
    public function copyStubs(): void
    {
        $composerJson = File::get(base_path() . "/composer.json");
        $hasKit = str($composerJson)->contains('jetstream') || str($composerJson)->contains('breeze') || str($composerJson)->contains('livewire/flux');

        if ($hasKit) {
            $this->warn('---------------------------------------------');
            $this->warn('🚨 Starter kit detected. Skipping demo components. 🚨');
            $this->warn('---------------------------------------------');

            return;
        }

        $this->info("Copying stubs...\n");

        $routes = base_path() . "{$this->ds}routes";
        $appViewComponents = "app{$this->ds}View{$this->ds}Components";
        $layoutsPath = "resources{$this->ds}views{$this->ds}layouts";
        $pagesPath = "resources{$this->ds}views{$this->ds}pages";

        // Blade Brand component
        $this->createDirectoryIfNotExists($appViewComponents);
        $this->copyFile(__DIR__ . "/../../../stubs/AppBrand.php", "{$appViewComponents}{$this->ds}AppBrand.php");

        // Default app layout
        $this->createDirectoryIfNotExists($layoutsPath);
        $this->copyFile(__DIR__ . "/../../../stubs/app.blade.php", "{$layoutsPath}{$this->ds}app.blade.php");

        // Livewire blade views
        $this->createDirectoryIfNotExists($pagesPath);

        // Demo component and its route
        $this->createDirectoryIfNotExists("$pagesPath{$this->ds}users");
        $this->copyFile(__DIR__ . "/../../../stubs/index.blade.php", "$pagesPath{$this->ds}users{$this->ds}index.blade.php");
        $this->copyFile(__DIR__ . "/../../../stubs/web.php", "$routes{$this->ds}web.php");
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

    public function checkForLaravelVersion(): void
    {
        if (version_compare(app()->version(), '12.0', '<')) {
            $this->error("❌  Laravel 12 or above required.");

            exit;
        }
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
