<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;
use Throwable;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected static bool $testDatabaseMigrated = false;

    protected function setUp(): void
    {
        parent::setUp();

        $this->prepareIsolatedTestDatabase();
        $this->resetIsolatedTestDatabase();
    }

    protected function prepareIsolatedTestDatabase(): void
    {
        if (self::$testDatabaseMigrated) {
            return;
        }

        $database = config('database.connections.sqlite.database');

        if ($database !== ':memory:' && ! file_exists($database)) {
            if (! is_dir(dirname($database))) {
                mkdir(dirname($database), 0755, true);
            }

            touch($database);
        }

        Artisan::call('migrate:fresh', [
            '--force' => true,
        ]);

        self::$testDatabaseMigrated = true;
    }

    protected function resetIsolatedTestDatabase(): void
    {
        if (class_exists(PermissionRegistrar::class)) {
            app(PermissionRegistrar::class)->forgetCachedPermissions();
        }

        DB::statement('PRAGMA foreign_keys = OFF');

        foreach ($this->testDatabaseTables() as $table) {
            DB::table($table)->delete();
        }

        try {
            DB::statement('DELETE FROM sqlite_sequence');
        } catch (Throwable) {
            // The table exists only after SQLite creates an autoincrement sequence.
        }

        DB::statement('PRAGMA foreign_keys = ON');

        if (class_exists(PermissionRegistrar::class)) {
            app(PermissionRegistrar::class)->forgetCachedPermissions();
        }
    }

    /**
     * @return array<int, string>
     */
    protected function test_database_tables(): array
    {
        return collect(DB::select(
            "SELECT name FROM sqlite_master WHERE type = 'table' AND name NOT LIKE 'sqlite_%' AND name != 'migrations'"
        ))
            ->pluck('name')
            ->all();
    }
}
