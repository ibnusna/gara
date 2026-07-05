<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use PDO;

class SetupGaraCommand extends Command
{
    




    protected $signature = 'gara:setup';

    




    protected $description = 'Automate 3 databases setup, migrations, and seeding';

    


    public function handle()
    {
        $this->info('Starting GARA Automated Database Setup...');

        
        $databases = [
            'auth_gara' => 'mysql_auth',
            'lms_pembelajaran' => 'mysql_apps',
            'asesmen_gara' => 'asesmen_gara' 
        ];
        
        $defaultConn = config('database.default');
        if ($defaultConn && !in_array($defaultConn, $databases)) {
            $defaultDb = config("database.connections.{$defaultConn}.database");
            if ($defaultDb) {
                $databases[$defaultDb] = $defaultConn;
            }
        }

        $this->info('1. Memeriksa Koneksi & Auto-Create Database (Localhost Support)...');
        foreach ($databases as $dbName => $connectionName) {
            $actualDbName = config("database.connections.{$connectionName}.database", $dbName);
            $host = config("database.connections.{$connectionName}.host", '127.0.0.1');
            $port = config("database.connections.{$connectionName}.port", '3306');
            $user = config("database.connections.{$connectionName}.username", 'root');
            $pass = config("database.connections.{$connectionName}.password", '');

            try {
                
                $pdo = new PDO("mysql:host={$host};port={$port}", $user, $pass);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $pdo->exec("DROP DATABASE IF EXISTS `{$actualDbName}`;");
                $pdo->exec("CREATE DATABASE `{$actualDbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
            } catch (\Exception $e) {
                
                
            }

            try {
                
                DB::connection($connectionName)->getPdo();
                $this->line(" - Koneksi `{$actualDbName}` [{$connectionName}] OK.");
            } catch (\Exception $e) {
                $this->error("KONEKSI GAGAL ke [{$connectionName}] dengan DB: {$actualDbName}.");
                $this->error("Error: " . $e->getMessage());
                return; 
            }
        }

        $this->info('2. Importing Schema from local SQL dumps...');
        $sqlFiles = [
            'mysql_auth' => database_path('sql/auth_gara.sql'),
            'mysql_apps' => database_path('sql/lms_pembelajaran.sql'),
            'asesmen_gara' => database_path('sql/asesmen_gara.sql')
        ];

        foreach ($sqlFiles as $connection => $file) {
            if (file_exists($file)) {
                $this->line(" - Importing schema into connection: {$connection}");
                $sql = file_get_contents($file);

                
                DB::connection($connection)->statement('SET FOREIGN_KEY_CHECKS=0;');
                DB::connection($connection)->unprepared($sql);
                DB::connection($connection)->statement('SET FOREIGN_KEY_CHECKS=1;');

                $this->info("   > Imported {$connection}");
            } else {
                $this->warn(" - Warning: SQL dump not found at {$file}");
            }
        }

        $this->info('3. Pre-registering (faking) Laravel migrations to prevent schema collision...');
        try {
            $migrator = app('migrator');
            $files = $migrator->getMigrationFiles(database_path('migrations'));
            $repository = $migrator->getRepository();

            foreach ($files as $name => $file) {
                $resolveMethod = new \ReflectionMethod($migrator, 'resolvePath');
                $resolveMethod->setAccessible(true);
                $migration = $resolveMethod->invoke($migrator, $file);
                
                $connection = $migration->getConnection() ?: config('database.default');

                
                if (!config("database.connections.{$connection}")) {
                    if ($connection === 'mysql_asesmen' && config('database.connections.asesmen_gara')) {
                        $connection = 'asesmen_gara';
                    }
                }

                $repository->setSource($connection);
                if (!$repository->repositoryExists()) {
                    $repository->createRepository();
                }

                
                $exists = DB::connection($connection)->table('migrations')->where('migration', $name)->exists();
                if (!$exists) {
                    DB::connection($connection)->table('migrations')->insert([
                        'migration' => $name,
                        'batch' => 1
                    ]);
                    $this->line("   - Registered: `{$name}` on connection: `{$connection}`");
                } else {
                    $this->line("   - Already registered: `{$name}` on connection: `{$connection}`");
                }
            }
            $this->info('   > Migration sync/registration completed.');
        } catch (\Exception $e) {
            $this->error('Failed to pre-register migrations: ' . $e->getMessage());
            return;
        }

        $this->info('4. Seeding Initial Accounts (Data Akun Awal)...');
        Artisan::call('db:seed', ['--force' => true]);
        $this->line(Artisan::output());

        $this->info('====================================================');
        $this->info('GARA Setup completed successfully!');
        $this->info('All 3 databases are now up and running.');
        $this->info('====================================================');
    }
}
