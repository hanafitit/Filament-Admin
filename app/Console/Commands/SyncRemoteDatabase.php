<?php

namespace App\Console\Commands;

use App\Support\Database\DatabaseModeManager;
use App\Support\Database\DatabaseSyncService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Throwable;

class SyncRemoteDatabase extends Command
{
    protected $signature = 'app:sync-remote-database
        {--force : Run even if the current database mode is not hybrid}
        {--keep-extra : Do not delete rows that are missing in the local database}';

    protected $description = 'Synchronize the local database into the remote database.';

    public function handle(DatabaseSyncService $syncService, DatabaseModeManager $modeManager): int
    {
        if (! $modeManager->isHybrid() && ! $this->option('force')) {
            $this->components->warn('Hybrid mode is disabled. Use --force to run manual synchronization.');
            Log::warning('Remote database sync skipped because hybrid mode is disabled.', [
                'mode' => $modeManager->current(),
                'forced' => (bool) $this->option('force'),
            ]);

            return self::SUCCESS;
        }

        Log::info('Remote database sync started.', [
            'delete_missing' => ! $this->option('keep-extra'),
            'mode' => $modeManager->current(),
        ]);

        try {
            $summary = $syncService->syncLocalToRemote(! $this->option('keep-extra'));
        } catch (Throwable $exception) {
            report($exception);
            $this->components->error('Database sync failed: '.$exception->getMessage());
            Log::error('Remote database sync failed.', [
                'message' => $exception->getMessage(),
                'exception' => $exception::class,
            ]);

            return self::FAILURE;
        }

        foreach ($summary as $table => $stats) {
            $this->line(sprintf(
                '%s: source=%d, upserted=%d, deleted=%d',
                $table,
                $stats['source_rows'] ?? 0,
                $stats['upserted'] ?? 0,
                $stats['deleted'] ?? 0,
            ));
        }

        Log::info('Remote database sync completed.', [
            'tables' => $summary,
        ]);

        $this->components->info('Remote database sync completed.');

        return self::SUCCESS;
    }
}
