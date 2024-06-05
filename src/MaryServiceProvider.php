<?php

namespace Mary;

use Arr;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Mary\Console\Commands\MaryBootcampCommand;
use Mary\Console\Commands\MaryInstallCommand;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MaryServiceProvider extends ServiceProvider
{
    // List of components that should have a special alias
    // No matter if components has custom prefix or not,
    // we also register alias for bellow componentsto avoid naming collision,
    // because they are used inside some Mary's components itself.
    protected $specialComponents = [
        'Button',
        'Card',
        'Icon',
        'Input',
        'ListItem',
        'Modal',
        'Menu',
        'MenuItem',
        'Header',
    ];

    // Prefix for special components
    protected $specialPrefix = 'mary-';

    /**
     * Perform post-registration booting of services.
     */
    public function boot(): void
    {
        $this->registerComponents();
        $this->registerBladeDirectives();

        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');

        // Publishing is only necessary when using the CLI.
        if ($this->app->runningInConsole()) {
            $this->bootForConsole();
        }
    }

    public function registerComponents()
    {
        // Rename the <x-icon> component provided by BladeUI Icons to <x-svg> to avoid conflicts with our own <x-icon>
        Blade::component('BladeUI\Icons\Components\Icon', 'svg');

        // Define the path to the directory containing the components in the vendor directory
        $componentsPath = base_path('vendor/robsontenorio/mary/src/View/Components');

        // Base namespace for the components
        $namespace = 'Mary\\View\\Components\\';

        // Iterate over all files in the components directory
        foreach (File::allFiles($componentsPath) as $file) {
            // Get the file name without its extension
            $fileName = $file->getFilenameWithoutExtension();

            // Construct the full class name of the component
            $componentClass = $namespace . $fileName;

            // Convert the file name to kebab-case for the component alias
            $componentAlias = Str::kebab($fileName);

            // Register the Blade component with its kebab-case alias
            Blade::component($componentAlias, $componentClass);

            // Check if the component is in the special components list
            if (in_array($fileName, $this->specialComponents)) {
                // Register the special alias for the component
                $specialAlias = $this->specialPrefix . $componentAlias;
                Blade::component($specialAlias, $componentClass);
            }
        }
    }

    public function registerBladeDirectives(): void
    {
        $this->registerScopeDirective();
    }

    public function registerScopeDirective(): void
    {
        /**
         * All credits from this blade directive goes to Konrad Kalemba.
         * Just copied and modified for my very specific use case.
         *
         * https://github.com/konradkalemba/blade-components-scoped-slots
         */
        Blade::directive('scope', function ($expression) {
            // Split the expression by `top-level` commas (not in parentheses)
            $directiveArguments = preg_split("/,(?![^\(\(]*[\)\)])/", $expression);
            $directiveArguments = array_map('trim', $directiveArguments);

            [$name, $functionArguments] = $directiveArguments;

            // Build function "uses" to inject extra external variables
            $uses = Arr::except(array_flip($directiveArguments), [$name, $functionArguments]);
            $uses = array_flip($uses);
            array_push($uses, '$__env');
            $uses = implode(',', $uses);

            /**
             *  Slot names can`t contains dot , eg: `user.city`.
             *  So we convert `user.city` to `user___city`
             *
             *  Later, on component it will be replaced back.
             */
            $name = str_replace('.', '___', $name);

            return "<?php \$__env->slot({$name}, function({$functionArguments}) use ({$uses}) { ?>";
        });

        Blade::directive('endscope', function () {
            return '<?php }); ?>';
        });
    }

    /**
     * Register any package services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/mary.php', 'mary');

        // Register the service the package provides.
        $this->app->singleton('mary', function ($app) {
            return new Mary();
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['mary'];
    }

    /**
     * Console-specific booting.
     */
    protected function bootForConsole(): void
    {
        // Publishing the configuration file.
        $this->publishes([
            __DIR__ . '/../config/mary.php' => config_path('mary.php'),
        ], 'mary.config');

        $this->commands([MaryInstallCommand::class, MaryBootcampCommand::class]);
    }
}
