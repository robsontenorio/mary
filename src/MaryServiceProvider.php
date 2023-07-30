<?php

namespace Mary;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\File;
use Illuminate\Support\ServiceProvider;
use Mary\View\Components\Alert;
use Mary\View\Components\Button;
use Mary\View\Components\Card;
use Mary\View\Components\Drawer;
use Mary\View\Components\Form;
use Mary\View\Components\Header;
use Mary\View\Components\Input;
use Mary\View\Components\ListItem;
use Mary\View\Components\Modal;
use Mary\View\Components\Nav;
use Mary\View\Components\Select;
use Mary\View\Components\Tab;
use Mary\View\Components\Tabs;
use Mary\View\Components\Toggle;

class MaryServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     */
    public function boot(): void
    {
        $this->registerBladeDirective();
        $this->registerComponents();

        // Publishing is only necessary when using the CLI.
        if ($this->app->runningInConsole()) {
            $this->bootForConsole();
        }
    }

    public function registerComponents()
    {
        Blade::component('alert', Alert::class);
        Blade::component('button', Button::class);
        Blade::component('card', Card::class);
        Blade::component('drawer', Drawer::class);
        Blade::component('form', Form::class);
        Blade::component('header', Header::class);
        Blade::component('input', Input::class);
        Blade::component('list-item', ListItem::class);
        Blade::component('modal', Modal::class);
        Blade::component('nav', Nav::class);
        Blade::component('select', Select::class);
        Blade::component('tab', Tab::class);
        Blade::component('tabs', Tabs::class);
        Blade::component('toggle', Toggle::class);
    }

    public function registerBladeDirective()
    {
        try {
            $manifest = File::json(public_path().'/build/manifest.json');
            $file = $manifest['vendor/robsontenorio/mary/resources/css/mary.css']['file'];
            $content = public_path().'/build/'.$file;

            Blade::directive('mary', function (string $expression) use ($file, $content) {
                return '<style data="'.$file.'">'.file_get_contents($content).'</style>';
            });
        } catch (\Throwable $th) {

            // An erro means there is no build files on main app, so write empty content
            Blade::directive('mary', function (string $expression) {
                return '';
            });
        }
    }

    /**
     * Register any package services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/mary.php', 'mary');

        // Register the service the package provides.
        $this->app->singleton('mary', function ($app) {
            return new Mary;
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
            __DIR__.'/../config/mary.php' => config_path('mary.php'),
        ], 'mary.config');
    }
}
