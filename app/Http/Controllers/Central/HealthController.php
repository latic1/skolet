<?php

declare(strict_types=1);

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

final class HealthController extends Controller
{
    public function check(): JsonResponse
    {
        $checks = [
            'db'      => $this->checkDatabase(),
            'cache'   => $this->checkCache(),
            'storage' => $this->checkStorage(),
        ];

        $failing = array_filter($checks, fn ($v) => $v !== 'ok');
        $status  = match (true) {
            count($failing) === count($checks) => 'fail',
            count($failing) > 0               => 'degraded',
            default                           => 'ok',
        };

        return response()->json(
            ['status' => $status, 'checks' => $checks],
            $status === 'fail' ? 503 : 200
        );
    }

    public function ping(): \Illuminate\Http\Response
    {
        return response('pong', 200)->header('Content-Type', 'text/plain');
    }

    private function checkDatabase(): string
    {
        try {
            DB::connection('central')->getPdo();
            return 'ok';
        } catch (Throwable) {
            return 'fail';
        }
    }

    private function checkCache(): string
    {
        try {
            Cache::put('__health', 1, 5);
            return Cache::get('__health') === 1 ? 'ok' : 'fail';
        } catch (Throwable) {
            return 'fail';
        }
    }

    private function checkStorage(): string
    {
        try {
            Storage::disk('local')->put('__health', '1');
            Storage::disk('local')->delete('__health');
            return 'ok';
        } catch (Throwable) {
            return 'fail';
        }
    }
}
