<?php

namespace App\Support\Database;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

class DatabaseSyncService
{
    public function syncLocalToRemote(bool $deleteMissing = true): array
    {
        $source = (string) config('database.local_connection');
        $target = (string) config('database.remote_connection');

        if ($source === $target) {
            throw new RuntimeException('Local and remote database connections must be different.');
        }

        $tables = $this->tables();
        $summary = [];

        foreach ($tables as $table) {
            $summary[$table['name']] = $this->upsertTable($source, $target, $table);
        }

        if ($deleteMissing) {
            foreach (array_reverse($tables) as $table) {
                $deleted = $this->deleteMissingRows($source, $target, $table);
                $summary[$table['name']]['deleted'] = $deleted;
            }
        }

        foreach ($tables as $table) {
            $this->syncSequenceIfNeeded($target, $table);
        }

        return $summary;
    }

    /**
     * @return array<int, array{name: string, key: array<int, string>, exclude?: array<int, string>}>
     */
    public function tables(): array
    {
        $tableNames = (array) config('permission.table_names', []);
        $columnNames = (array) config('permission.column_names', []);
        $teamsEnabled = (bool) config('permission.teams', false);
        $modelKey = $columnNames['model_morph_key'] ?? 'model_id';
        $rolePivotKey = $columnNames['role_pivot_key'] ?? 'role_id';
        $permissionPivotKey = $columnNames['permission_pivot_key'] ?? 'permission_id';
        $teamKey = $columnNames['team_foreign_key'] ?? 'team_id';

        $modelPermissionKey = $teamsEnabled
            ? [$teamKey, $permissionPivotKey, $modelKey, 'model_type']
            : [$permissionPivotKey, $modelKey, 'model_type'];

        $modelRoleKey = $teamsEnabled
            ? [$teamKey, $rolePivotKey, $modelKey, 'model_type']
            : [$rolePivotKey, $modelKey, 'model_type'];

        return array_values(array_filter([
            ['name' => 'users', 'key' => ['id']],
            ['name' => 'password_reset_tokens', 'key' => ['email']],
            ['name' => 'sources', 'key' => ['id']],
            ['name' => 'statuses', 'key' => ['id']],
            ['name' => $tableNames['permissions'] ?? 'permissions', 'key' => ['id']],
            ['name' => $tableNames['roles'] ?? 'roles', 'key' => ['id']],
            ['name' => $tableNames['role_has_permissions'] ?? 'role_has_permissions', 'key' => [$permissionPivotKey, $rolePivotKey]],
            ['name' => $tableNames['model_has_permissions'] ?? 'model_has_permissions', 'key' => $modelPermissionKey],
            ['name' => $tableNames['model_has_roles'] ?? 'model_has_roles', 'key' => $modelRoleKey],
            ['name' => 'orders', 'key' => ['id'], 'exclude' => ['net_income']],
            ['name' => 'order_comments', 'key' => ['id']],
            ['name' => 'order_attachments', 'key' => ['id']],
        ], fn (array $table): bool => filled($table['name'])));
    }

    /**
     * @param  array{name: string, key: array<int, string>, exclude?: array<int, string>}  $table
     * @return array{source_rows: int, upserted: int, deleted?: int}
     */
    protected function upsertTable(string $source, string $target, array $table): array
    {
        $tableName = $table['name'];
        $rows = $this->readRows($source, $tableName);
        $columns = $this->sharedColumns($source, $target, $tableName, $table['exclude'] ?? []);
        $payload = $rows->map(fn (array $row): array => array_intersect_key($row, array_flip($columns)));
        $updateColumns = array_values(array_diff($columns, $table['key']));

        if ($payload->isEmpty()) {
            return [
                'source_rows' => 0,
                'upserted' => 0,
            ];
        }

        foreach ($payload->chunk(200) as $chunk) {
            $query = DB::connection($target)->table($tableName);

            if ($updateColumns === []) {
                $query->insertOrIgnore($chunk->all());
                continue;
            }

            $query->upsert($chunk->all(), $table['key'], $updateColumns);
        }

        return [
            'source_rows' => $payload->count(),
            'upserted' => $payload->count(),
        ];
    }

    /**
     * @param  array{name: string, key: array<int, string>, exclude?: array<int, string>}  $table
     */
    protected function deleteMissingRows(string $source, string $target, array $table): int
    {
        $tableName = $table['name'];
        $keys = $table['key'];
        $sourceKeys = $this->readKeyMap($source, $tableName, $keys);
        $targetKeys = $this->readKeyMap($target, $tableName, $keys);
        $toDelete = array_values(array_diff(array_keys($targetKeys), array_keys($sourceKeys)));

        if ($toDelete === []) {
            return 0;
        }

        foreach (array_chunk($toDelete, 200) as $chunk) {
            DB::connection($target)->transaction(function () use ($target, $tableName, $keys, $chunk, $targetKeys): void {
                foreach ($chunk as $signature) {
                    $query = DB::connection($target)->table($tableName);

                    foreach ($keys as $column) {
                        $query->where($column, $targetKeys[$signature][$column]);
                    }

                    $query->delete();
                }
            });
        }

        return count($toDelete);
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    protected function readRows(string $connection, string $table): Collection
    {
        return DB::connection($connection)
            ->table($table)
            ->get()
            ->map(fn (object $row): array => (array) $row)
            ->values();
    }

    /**
     * @param  array<int, string>  $keys
     * @return array<string, array<string, mixed>>
     */
    protected function readKeyMap(string $connection, string $table, array $keys): array
    {
        return DB::connection($connection)
            ->table($table)
            ->select($keys)
            ->get()
            ->mapWithKeys(function (object $row) use ($keys): array {
                $payload = (array) $row;
                $signature = implode('|', array_map(
                    fn (string $key): string => "{$key}:".($payload[$key] ?? ''),
                    $keys,
                ));

                return [$signature => $payload];
            })
            ->all();
    }

    /**
     * @return array<int, string>
     */
    protected function sharedColumns(string $source, string $target, string $table, array $exclude = []): array
    {
        $sourceColumns = Schema::connection($source)->getColumnListing($table);
        $targetColumns = Schema::connection($target)->getColumnListing($table);

        return array_values(array_diff(array_intersect($sourceColumns, $targetColumns), $exclude));
    }

    /**
     * @param  array{name: string, key: array<int, string>, exclude?: array<int, string>}  $table
     */
    protected function syncSequenceIfNeeded(string $target, array $table): void
    {
        if ($table['key'] !== ['id']) {
            return;
        }

        $connection = DB::connection($target);

        if ($connection->getDriverName() !== 'pgsql') {
            return;
        }

        $tableName = $table['name'];
        $wrappedTable = $connection->getQueryGrammar()->wrapTable($tableName);

        $connection->statement(
            "SELECT setval(pg_get_serial_sequence('{$tableName}', 'id'), COALESCE(MAX(id), 1), MAX(id) IS NOT NULL) FROM {$wrappedTable}"
        );
    }
}
