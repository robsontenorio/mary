<?php

namespace Mary\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
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
        $this->copyFile(__DIR__ . "/../../../stubs/bootcamp/2024_01_27_023021_create_countries_table.php",
            "database{$this->ds}migrations{$this->ds}2024_01_27_023021_create_countries_table.php");

        $this->copyFile(__DIR__ . "/../../../stubs/bootcamp/2014_10_12_000000_create_users_table.php",
            "database{$this->ds}migrations{$this->ds}2014_10_12_000000_create_users_table.php");

        $this->copyFile(__DIR__ . "/../../../stubs/bootcamp/User.php", "app{$this->ds}Models{$this->ds}User.php");
        $this->copyFile(__DIR__ . "/../../../stubs/bootcamp/Country.php", "app{$this->ds}Models{$this->ds}Country.php");
        $this->copyFile(__DIR__ . "/../../../stubs/bootcamp/UserFactory.php", "database{$this->ds}factories{$this->ds}UserFactory.php");
        $this->copyFile(__DIR__ . "/../../../stubs/bootcamp/CountrySeeder.php", "database{$this->ds}seeders{$this->ds}CountrySeeder.php");
        $this->copyFile(__DIR__ . "/../../../stubs/bootcamp/DatabaseSeeder.php", "database{$this->ds}seeders{$this->ds}DatabaseSeeder.php");

        // Setup SQLITE
        $envPath = base_path() . "{$this->ds}.env";
        $env = File::get($envPath);
        $content = str($env)->replace('DB_CONNECTION=mysql', 'DB_CONNECTION=sqlite');
        File::put($envPath, $content);

        // Create database file
        Process::run("touch database/database.sqlite", function (string $type, string $output) {
            echo $output;
        })->throw();

        // Migrate fresh seed
        Artisan::call('migrate:fresh --seed');

        // Clear view cache
        Artisan::call('view:clear');

        $this->info("\n✅   Done! Go back to Bootcamp page\n");
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
