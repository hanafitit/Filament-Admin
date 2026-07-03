<?php

namespace Tests\Feature;

use App\Support\Database\DatabaseModeManager;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class DatabaseModeManagerTest extends TestCase
{
    public function test_it_applies_hybrid_mode_to_local_connection(): void
    {
        Config::set('database.mode', DatabaseModeManager::MODE_HYBRID);
        Config::set('database.local_connection', 'sqlite');
        Config::set('database.remote_connection', 'pgsql_remote');

        $manager = app(DatabaseModeManager::class);
        $appliedMode = $manager->apply();

        $this->assertSame(DatabaseModeManager::MODE_HYBRID, $appliedMode);
        $this->assertSame('sqlite', config('database.default'));
        $this->assertTrue($manager->isHybrid());
    }
}
