<?php

namespace App\Console\Commands;

use App\Support\Database\DatabaseSyncService;
use Illuminate\Console\Command;
use Throwable;

class PullRemoteDatabase extends Command
{
    protected $signature = 'app:pull-remote-database
        {--keep-extra : Do not delete rows that are missing in the remote database}';

    protected $description = 'Synchronize the remote database into the local database.';

    public function handle(DatabaseSyncService $syncService): int
    {
        try {
            $summary = $syncService->syncRemoteToLocal(! $this->option('keep-extra'));
        } catch (Throwable $exception) {
            report($exception);
            $this->components->error('Database pull failed: '.$exception->getMessage());

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

        $this->components->info('Remote database pull completed.');

        return self::SUCCESS;
    }
}
