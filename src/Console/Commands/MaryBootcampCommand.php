<?php

namespace Mary\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Process;
use RuntimeException;

class MaryBootcampCommand extends Command
{
    protected $signature = 'mary:bootcamp';

    protected $description = 'Prepare the app for the bootcamp';

    protected $ds = DIRECTORY_SEPARATOR;

    public function handle()
    {
        $this->info("❤️  maryUI bootcamp install");

        $web = base_path() . "{$this->ds}routes{$this->ds}web.php";

        // If there is something in there, skip install
        if (count(file($web)) > 20) {
            $this->warn("Bootcamp install works only on fresh new apps. \n");

            return;
        }

        // Make sure it have Livewire ant Volt
        $this->info("Making sure you have Livewire and Volt ...\n");

        Process::run("composer require livewire/livewire livewire/volt && php artisan volt:install", function (string $type, string $output) {
            echo $output;
        })->throw();

        // Copy stubs
        $this->copyFile(__DIR__ . "/../../../stubs/bootcamp/2024_01_01_000001_bootcamp.php",
            "database{$this->ds}migrations{$this->ds}2024_01_01_000001_bootcamp.php");

        $this->copyFile(__DIR__ . "/../../../stubs/bootcamp/User.php", "app{$this->ds}Models{$this->ds}User.php");
        $this->copyFile(__DIR__ . "/../../../stubs/bootcamp/Language.php", "app{$this->ds}Models{$this->ds}Language.php");
        $this->copyFile(__DIR__ . "/../../../stubs/bootcamp/Country.php", "app{$this->ds}Models{$this->ds}Country.php");
        $this->copyFile(__DIR__ . "/../../../stubs/bootcamp/UserFactory.php", "database{$this->ds}factories{$this->ds}UserFactory.php");
        $this->copyFile(__DIR__ . "/../../../stubs/bootcamp/CountrySeeder.php", "database{$this->ds}seeders{$this->ds}CountrySeeder.php");
        $this->copyFile(__DIR__ . "/../../../stubs/bootcamp/LanguageSeeder.php", "database{$this->ds}seeders{$this->ds}LanguageSeeder.php");
        $this->copyFile(__DIR__ . "/../../../stubs/bootcamp/DatabaseSeeder.php", "database{$this->ds}seeders{$this->ds}DatabaseSeeder.php");
        $this->copyFile(__DIR__ . "/../../../stubs/bootcamp/empty-user.jpg", "public{$this->ds}empty-user.jpg");

        // Migrate fresh seed
        Artisan::call('migrate:fresh --seed');

        // Clear view cache
        Artisan::call('view:clear');

        $this->info("\n✅   Done! Go back to Bootcamp page.\n");
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
