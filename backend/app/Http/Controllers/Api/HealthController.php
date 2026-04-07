<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class HealthController extends Controller
{
    /**
     * GET /api/health
     *
     * Public — no auth required.
     * Returns 200 when all checks pass, 503 when any check fails.
     * Designed for uptime monitors (UptimeRobot, Betterstack, AWS ALB, etc.)
     */
    public function __invoke(): JsonResponse
    {
        $checks   = [];
        $healthy  = true;
        $start    = hrtime(true);

        // -------------------------------------------------------------------------
        // Database
        // -------------------------------------------------------------------------
        try {
            $dbStart = hrtime(true);

            DB::selectOne('SELECT 1');

            $checks['database'] = [
                'status'    => 'ok',
                'latency_ms' => $this->elapsedMs($dbStart),
            ];
        } catch (\Throwable $e) {
            $healthy = false;

            $checks['database'] = [
                'status'  => 'fail',
                'message' => 'Database unreachable',
            ];
        }

        // -------------------------------------------------------------------------
        // Cache (optional but recommended — catches Redis/file permission issues)
        // -------------------------------------------------------------------------
        try {
            $cacheStart = hrtime(true);
            $key = 'health:probe:' . uniqid();

            Cache::put($key, 1, 5);
            Cache::forget($key);

            $checks['cache'] = [
                'status'     => 'ok',
                'latency_ms' => $this->elapsedMs($cacheStart),
            ];
        } catch (\Throwable $e) {
            // Cache failure is a warning, not fatal — app can run without it
            // Change $healthy = false here if your app requires cache
            $checks['cache'] = [
                'status'  => 'warn',
                'message' => 'Cache store unavailable',
            ];
        }

        // -------------------------------------------------------------------------
        // Storage (checks the default disk is writable)
        // -------------------------------------------------------------------------
        try {
            $storageStart = hrtime(true);
            $probe = 'health-probe-' . uniqid() . '.tmp';

            \Illuminate\Support\Facades\Storage::put($probe, '1');
            \Illuminate\Support\Facades\Storage::delete($probe);

            $checks['storage'] = [
                'status'     => 'ok',
                'latency_ms' => $this->elapsedMs($storageStart),
            ];
        } catch (\Throwable $e) {
            $healthy = false;

            $checks['storage'] = [
                'status'  => 'fail',
                'message' => 'Storage disk not writable',
            ];
        }

        // -------------------------------------------------------------------------
        // Response
        // -------------------------------------------------------------------------
        $status = $healthy ? 200 : 503;

        return response()->json([
            'status'      => $healthy ? 'ok' : 'fail',
            'environment' => app()->environment(),
            'timestamp'   => now()->toIso8601String(),
            'latency_ms'  => $this->elapsedMs($start),
            'checks'      => $checks,
        ], $status, [
            // Prevent uptime monitors or CDNs from caching this response
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
            'Pragma'        => 'no-cache',
        ]);
    }

    private function elapsedMs(int $startNs): float
    {
        return round((hrtime(true) - $startNs) / 1_000_000, 2);
    }
}
