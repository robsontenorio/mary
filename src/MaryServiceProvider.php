<?php

namespace Mary;

use Arr;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Mary\Console\Commands\MaryBootcampCommand;
use Mary\Console\Commands\MaryInstallCommand;
use Mary\View\Components\Accordion;
use Mary\View\Components\Alert;
use Mary\View\Components\Avatar;
use Mary\View\Components\Badge;
use Mary\View\Components\Button;
use Mary\View\Components\Calendar;
use Mary\View\Components\Card;
use Mary\View\Components\Chart;
use Mary\View\Components\Checkbox;
use Mary\View\Components\Choices;
use Mary\View\Components\ChoicesOffline;
use Mary\View\Components\Collapse;
use Mary\View\Components\Colorpicker;
use Mary\View\Components\DatePicker;
use Mary\View\Components\DateTime;
use Mary\View\Components\Diff;
use Mary\View\Components\Drawer;
use Mary\View\Components\Dropdown;
use Mary\View\Components\Editor;
use Mary\View\Components\Errors;
use Mary\View\Components\File;
use Mary\View\Components\Form;
use Mary\View\Components\Header;
use Mary\View\Components\Hr;
use Mary\View\Components\Icon;
use Mary\View\Components\ImageGallery;
use Mary\View\Components\ImageLibrary;
use Mary\View\Components\Input;
use Mary\View\Components\Kbd;
use Mary\View\Components\ListItem;
use Mary\View\Components\Loading;
use Mary\View\Components\Main;
use Mary\View\Components\Markdown;
use Mary\View\Components\Menu;
use Mary\View\Components\MenuItem;
use Mary\View\Components\MenuSeparator;
use Mary\View\Components\MenuSub;
use Mary\View\Components\MenuTitle;
use Mary\View\Components\Modal;
use Mary\View\Components\Nav;
use Mary\View\Components\Pin;
use Mary\View\Components\Progress;
use Mary\View\Components\ProgressRadial;
use Mary\View\Components\Radio;
use Mary\View\Components\Range;
use Mary\View\Components\Select;
use Mary\View\Components\Signature;
use Mary\View\Components\Spotlight;
use Mary\View\Components\Stat;
use Mary\View\Components\Step;
use Mary\View\Components\Steps;
use Mary\View\Components\Tab;
use Mary\View\Components\Table;
use Mary\View\Components\Tabs;
use Mary\View\Components\Tags;
use Mary\View\Components\Textarea;
use Mary\View\Components\ThemeToggle;
use Mary\View\Components\TimelineItem;
use Mary\View\Components\Toast;
use Mary\View\Components\Toggle;

class MaryServiceProvider extends ServiceProvider
{
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
        // Just rename <x-icon> provided by BladeUI Icons to <x-svg> to not collide with ours
        Blade::component('BladeUI\Icons\Components\Icon', 'svg');

        // No matter if components has custom prefix or not,
        // we also register bellow alias to avoid naming collision,
        // because they are used inside some Mary's components itself.
        Blade::component('mary-button', Button::class);
        Blade::component('mary-card', Card::class);
        Blade::component('mary-icon', Icon::class);
        Blade::component('mary-input', Input::class);
        Blade::component('mary-list-item', ListItem::class);
        Blade::component('mary-modal', Modal::class);
        Blade::component('mary-menu', Menu::class);
        Blade::component('mary-menu-item', MenuItem::class);
        Blade::component('mary-header', Header::class);

        $prefix = config('mary.prefix');

        // Blade
        Blade::component($prefix . 'accordion', Accordion::class);
        Blade::component($prefix . 'alert', Alert::class);
        Blade::component($prefix . 'avatar', Avatar::class);
        Blade::component($prefix . 'badge', Badge::class);
        Blade::component($prefix . 'button', Button::class);
        Blade::component($prefix . 'calendar', Calendar::class);
        Blade::component($prefix . 'card', Card::class);
        Blade::component($prefix . 'chart', Chart::class);
        Blade::component($prefix . 'checkbox', Checkbox::class);
        Blade::component($prefix . 'choices', Choices::class);
        Blade::component($prefix . 'choices-offline', ChoicesOffline::class);
        Blade::component($prefix . 'collapse', Collapse::class);
        Blade::component($prefix . 'colorpicker', Colorpicker::class);
        Blade::component($prefix . 'datepicker', DatePicker::class);
        Blade::component($prefix . 'datetime', DateTime::class);
        Blade::component($prefix . 'diff', Diff::class);
        Blade::component($prefix . 'drawer', Drawer::class);
        Blade::component($prefix . 'dropdown', Dropdown::class);
        Blade::component($prefix . 'editor', Editor::class);
        Blade::component($prefix . 'errors', Errors::class);
        Blade::component($prefix . 'file', File::class);
        Blade::component($prefix . 'form', Form::class);
        Blade::component($prefix . 'header', Header::class);
        Blade::component($prefix . 'hr', Hr::class);
        Blade::component($prefix . 'icon', Icon::class);
        Blade::component($prefix . 'image-gallery', ImageGallery::class);
        Blade::component($prefix . 'image-library', ImageLibrary::class);
        Blade::component($prefix . 'input', Input::class);
        Blade::component($prefix . 'kbd', Kbd::class);
        Blade::component($prefix . 'list-item', ListItem::class);
        Blade::component($prefix . 'loading', Loading::class);
        Blade::component($prefix . 'markdown', Markdown::class);
        Blade::component($prefix . 'modal', Modal::class);
        Blade::component($prefix . 'menu', Menu::class);
        Blade::component($prefix . 'menu-item', MenuItem::class);
        Blade::component($prefix . 'menu-separator', MenuSeparator::class);
        Blade::component($prefix . 'menu-sub', MenuSub::class);
        Blade::component($prefix . 'menu-title', MenuTitle::class);
        Blade::component($prefix . 'main', Main::class);
        Blade::component($prefix . 'nav', Nav::class);
        Blade::component($prefix . 'pin', Pin::class);
        Blade::component($prefix . 'progress', Progress::class);
        Blade::component($prefix . 'progress-radial', ProgressRadial::class);
        Blade::component($prefix . 'radio', Radio::class);
        Blade::component($prefix . 'range', Range::class);
        Blade::component($prefix . 'select', Select::class);
        Blade::component($prefix . 'signature', Signature::class);
        Blade::component($prefix . 'spotlight', Spotlight::class);
        Blade::component($prefix . 'stat', Stat::class);
        Blade::component($prefix . 'steps', Steps::class);
        Blade::component($prefix . 'step', Step::class);
        Blade::component($prefix . 'table', Table::class);
        Blade::component($prefix . 'tab', Tab::class);
        Blade::component($prefix . 'tabs', Tabs::class);
        Blade::component($prefix . 'tags', Tags::class);
        Blade::component($prefix . 'textarea', Textarea::class);
        Blade::component($prefix . 'timeline-item', TimelineItem::class);
        Blade::component($prefix . 'theme-toggle', ThemeToggle::class);
        Blade::component($prefix . 'toast', Toast::class);
        Blade::component($prefix . 'toggle', Toggle::class);
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
            __DIR__ . '/../config/mary.php' => config_path('mary.php'),
        ], 'mary.config');

        $this->commands([MaryInstallCommand::class, MaryBootcampCommand::class]);
    }
}
