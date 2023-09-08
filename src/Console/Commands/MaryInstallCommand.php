<?php

namespace Mary\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Process;

class MaryInstallCommand extends Command
{
    protected $signature = 'mary:install';

    protected $description = 'Command description';

    public function handle()
    {
        $this->info("\n\n🔨 Installing Mary...\n\n");

        $directories = Process::tty()->run('mkdir -p app/Livewire && mkdir -p resources/views/components/layouts');
        $this->info($directories->output());

        $livewire = Process::tty()->run('composer require livewire/livewire');
        $this->info($livewire->output());

        $js = Process::tty()->run('yarn add -D tailwindcss daisyui@latest postcss autoprefixer && npx tailwindcss init -p');
        $this->info($js->output());

        $layout = Process::tty()->run('cp '.__DIR__.'/../../../stubs/app.blade.php resources/views/components/layouts/');
        $this->info($layout->output());

        $css = Process::tty()->run('cp '.__DIR__.'/../../../stubs/app.css resources/css/');
        $this->info($css->output());

        $tailwindConfig = Process::tty()->run('cp '.__DIR__.'/../../../stubs/tailwind.config.js .');
        $this->info($tailwindConfig->output());

        $welcome = Process::tty()->run('cp '.__DIR__.'/../../../stubs/Welcome.php app/Livewire/');
        $this->info($welcome->output());

        $route = Process::tty()->run('cp '.__DIR__.'/../../../stubs/web.php routes/');
        $this->info($route->output());

        $this->info('🌟 Done!');
        $this->info("\n\n==> Run `yarn dev`\n\n");

    }
}
