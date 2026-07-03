<?php

namespace Tests\Feature;

use App\Support\Diagnostics\RequestDiagnostics;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class RequestDiagnosticsTest extends TestCase
{
    public function test_begin_and_finish_redact_sensitive_context(): void
    {
        config()->set('logging.diagnostics.enabled', true);

        $captured = [];

        Log::shouldReceive('channel')->twice()->with('performance')->andReturnSelf();
        Log::shouldReceive('info')->twice()->andReturnUsing(function (string $message, array $context) use (&$captured): void {
            $captured[$message] = $context;
        });

        $diagnostics = new RequestDiagnostics;
        $request = Request::create('/admin/orders', 'GET');
        $request->server->set('REMOTE_ADDR', '127.0.0.1');
        $request->headers->set('User-Agent', str_repeat('A', 300));

        $diagnostics->begin($request);
        $diagnostics->finish($request, new Response('', 200));

        $startContext = $captured['http.request_started'];
        $finishContext = $captured['http.request'];

        $this->assertArrayNotHasKey('session_id', $startContext);
        $this->assertArrayNotHasKey('auth_user_email', $startContext);
        $this->assertArrayNotHasKey('host', $startContext['database']);
        $this->assertArrayNotHasKey('username', $startContext['database']);
        $this->assertSame(hash('sha256', '127.0.0.1'), $startContext['ip']);
        $this->assertSame(255, strlen($startContext['user_agent']));
        $this->assertArrayNotHasKey('session_id', $finishContext);
        $this->assertArrayNotHasKey('auth_user_email', $finishContext);
    }

    public function test_record_query_keeps_parameter_placeholders_in_logs(): void
    {
        config()->set('logging.diagnostics.enabled', true);
        config()->set('logging.diagnostics.log_all_queries', true);

        $loggedQuery = null;

        Log::shouldReceive('channel')->times(2)->with('performance')->andReturnSelf();
        Log::shouldReceive('info')->once();
        Log::shouldReceive('debug')->once()->andReturnUsing(function (string $message, array $context) use (&$loggedQuery): void {
            $this->assertSame('sql.query', $message);
            $loggedQuery = $context;
        });

        $diagnostics = new RequestDiagnostics;
        $request = Request::create('/orders', 'GET');

        $diagnostics->begin($request);
        $diagnostics->recordQuery(new QueryExecuted(
            'select * from users where email = ?',
            ['secret@example.com'],
            15.8,
            DB::connection(),
        ));

        $this->assertSame('select * from users where email = ?', $loggedQuery['sql']);
        $this->assertStringNotContainsString('secret@example.com', $loggedQuery['sql']);
    }

    public function test_disabled_diagnostics_skip_logging(): void
    {
        config()->set('logging.diagnostics.enabled', false);

        Log::shouldReceive('channel')->never();
        Log::shouldReceive('info')->never();
        Log::shouldReceive('debug')->never();
        Log::shouldReceive('error')->never();

        $diagnostics = new RequestDiagnostics;
        $request = Request::create('/orders', 'GET');

        $diagnostics->begin($request);
        $diagnostics->recordQuery(new QueryExecuted('select 1', [], 1.1, DB::connection()));
        $diagnostics->finish($request, new Response('', 200));

        $this->assertTrue(true);
    }
}
