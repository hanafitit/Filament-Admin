<?php

namespace App\Support\Database;

use Illuminate\Support\Facades\Config;
use InvalidArgumentException;

class DatabaseModeManager
{
    public const MODE_LOCAL = 'local';

    public const MODE_REMOTE = 'remote';

    public const MODE_HYBRID = 'hybrid';

    /**
     * @return array<int, string>
     */
    public static function supportedModes(): array
    {
        return [
            self::MODE_LOCAL,
            self::MODE_REMOTE,
            self::MODE_HYBRID,
        ];
    }

    public function current(): string
    {
        $configuredMode = (string) config('database.mode', self::MODE_LOCAL);

        if (in_array($configuredMode, self::supportedModes(), true)) {
            return $configuredMode;
        }

        $defaultConnection = (string) config('database.default');
        $remoteConnection = (string) config('database.remote_connection');

        return $defaultConnection === $remoteConnection
            ? self::MODE_REMOTE
            : self::MODE_LOCAL;
    }

    public function apply(?string $mode = null): string
    {
        $mode ??= $this->current();
        $this->assertSupported($mode);

        $defaultConnection = match ($mode) {
            self::MODE_REMOTE => (string) config('database.remote_connection'),
            self::MODE_LOCAL, self::MODE_HYBRID => (string) config('database.local_connection'),
        };

        Config::set('database.operation_mode', $mode);
        Config::set('database.default', $defaultConnection);
        Config::set('queue.connections.database.connection', $defaultConnection);
        Config::set('queue.failed.database', $defaultConnection);
        Config::set('session.connection', $defaultConnection);
        Config::set('cache.stores.database.connection', $defaultConnection);
        Config::set('cache.stores.database.lock_connection', $defaultConnection);
        Config::set('backup.backup.source.databases', [$defaultConnection]);

        return $mode;
    }

    public function isHybrid(?string $mode = null): bool
    {
        return ($mode ?? $this->current()) === self::MODE_HYBRID;
    }

    protected function assertSupported(string $mode): void
    {
        if (! in_array($mode, self::supportedModes(), true)) {
            throw new InvalidArgumentException("Unsupported database mode [{$mode}].");
        }
    }
}
