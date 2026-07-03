<?php

namespace App\Support\Diagnostics;

use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Throwable;

class RequestDiagnostics
{
    protected float $startedAt = 0.0;

    protected int $startedMemory = 0;

    protected string $requestId = '';

    protected ?string $method = null;

    protected ?string $path = null;

    protected ?string $routeName = null;

    protected array $queries = [];

    protected float $totalQueryTimeMs = 0.0;

    protected bool $started = false;

    protected ?string $ipAddress = null;

    protected ?string $userAgent = null;

    protected ?int $authenticatedUserId = null;

    public function begin(Request $request): void
    {
        if (! $this->isEnabled() || $this->started) {
            return;
        }

        $this->started = true;
        $this->startedAt = microtime(true);
        $this->startedMemory = memory_get_usage(true);
        $this->requestId = bin2hex(random_bytes(6));
        $this->method = $request->method();
        $this->path = '/'.ltrim($request->path(), '/');
        $this->routeName = $request->route()?->getName();
        $this->ipAddress = $this->maskIpAddress($request->ip());
        $this->userAgent = $this->truncateUserAgent($request->userAgent());
        $this->authenticatedUserId = Auth::id();

        $this->logger()->info('http.request_started', [
            'request_id' => $this->requestId,
            'method' => $this->method,
            'path' => $this->path,
            'route' => $this->routeName,
            'ip' => $this->ipAddress,
            'user_agent' => $this->userAgent,
            'auth_user_id' => $this->authenticatedUserId,
            'database' => $this->databaseContext(),
            'memory_start_mb' => round($this->startedMemory / 1024 / 1024, 2),
        ]);
    }

    public function recordQuery(QueryExecuted $query): void
    {
        if (! $this->isEnabled() || ! $this->started) {
            return;
        }

        $timeMs = round($query->time, 2);
        $this->totalQueryTimeMs += $timeMs;
        $sequence = count($this->queries) + 1;
        $elapsedMs = round((microtime(true) - $this->startedAt) * 1000, 2);

        $entry = [
            'request_id' => $this->requestId,
            'sequence' => $sequence,
            'connection' => $query->connectionName,
            'time_ms' => $timeMs,
            'elapsed_since_request_start_ms' => $elapsedMs,
            'cumulative_query_time_ms' => round($this->totalQueryTimeMs, 2),
            'sql' => $query->sql,
            'connection_meta' => $this->databaseContext($query->connectionName),
        ];

        $this->queries[] = $entry;

        if ($this->shouldLogAllQueries() || $timeMs >= $this->slowQueryThreshold()) {
            $this->logger()->debug('sql.query', $entry);
        }
    }

    public function finish(Request $request, SymfonyResponse $response): void
    {
        if (! $this->isEnabled() || ! $this->started) {
            return;
        }

        $durationMs = round((microtime(true) - $this->startedAt) * 1000, 2);
        $peakMemoryMb = round(memory_get_peak_usage(true) / 1024 / 1024, 2);
        $memoryDeltaMb = round((memory_get_usage(true) - $this->startedMemory) / 1024 / 1024, 2);

        $this->logger()->info('http.request', [
            'request_id' => $this->requestId,
            'method' => $this->method ?? $request->method(),
            'path' => $this->path ?? ('/'.ltrim($request->path(), '/')),
            'route' => $this->routeName ?? $request->route()?->getName(),
            'ip' => $this->ipAddress ?? $request->ip(),
            'user_agent' => $this->userAgent ?? $request->userAgent(),
            'auth_user_id' => $this->authenticatedUserId,
            'status' => $response->getStatusCode(),
            'duration_ms' => $durationMs,
            'query_count' => count($this->queries),
            'query_time_ms' => round($this->totalQueryTimeMs, 2),
            'memory_delta_mb' => $memoryDeltaMb,
            'memory_peak_mb' => $peakMemoryMb,
            'database' => $this->databaseContext(),
            'queries' => $this->queries,
            'top_queries' => $this->topQueries(),
            'slow_queries' => array_values(array_filter(
                $this->queries,
                fn (array $query): bool => $query['time_ms'] >= $this->slowQueryThreshold()
            )),
        ]);
    }

    public function fail(Request $request, Throwable $exception): void
    {
        if (! $this->isEnabled() || ! $this->started) {
            return;
        }

        $durationMs = round((microtime(true) - $this->startedAt) * 1000, 2);

        $this->logger()->error('http.request_failed', [
            'request_id' => $this->requestId,
            'method' => $this->method ?? $request->method(),
            'path' => $this->path ?? ('/'.ltrim($request->path(), '/')),
            'route' => $this->routeName ?? $request->route()?->getName(),
            'ip' => $this->ipAddress ?? $request->ip(),
            'user_agent' => $this->userAgent ?? $request->userAgent(),
            'auth_user_id' => $this->authenticatedUserId,
            'duration_ms' => $durationMs,
            'query_count' => count($this->queries),
            'query_time_ms' => round($this->totalQueryTimeMs, 2),
            'database' => $this->databaseContext(),
            'queries' => $this->queries,
            'top_queries' => $this->topQueries(),
            'exception' => $exception::class,
            'message' => $exception->getMessage(),
        ]);
    }

    protected function logger()
    {
        return Log::channel((string) config('logging.diagnostics.channel', 'performance'));
    }

    protected function isEnabled(): bool
    {
        return (bool) config('logging.diagnostics.enabled', false);
    }

    protected function shouldLogAllQueries(): bool
    {
        return (bool) config('logging.diagnostics.log_all_queries', false);
    }

    protected function slowQueryThreshold(): float
    {
        return (float) config('logging.diagnostics.slow_query_ms', 250);
    }

    protected function databaseContext(?string $connection = null): array
    {
        $connectionName = $connection ?: config('database.default');
        $config = config("database.connections.{$connectionName}", []);

        return [
            'connection' => $connectionName,
            'driver' => $config['driver'] ?? null,
        ];
    }

    protected function topQueries(): array
    {
        $queries = $this->queries;

        usort($queries, fn (array $left, array $right): int => $right['time_ms'] <=> $left['time_ms']);

        return array_slice($queries, 0, 10);
    }

    protected function maskIpAddress(?string $ipAddress): ?string
    {
        if (blank($ipAddress)) {
            return null;
        }

        return hash('sha256', (string) $ipAddress);
    }

    protected function truncateUserAgent(?string $userAgent): ?string
    {
        if (blank($userAgent)) {
            return null;
        }

        return mb_substr((string) $userAgent, 0, 255);
    }
}
