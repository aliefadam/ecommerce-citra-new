<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

class OperationalHealthController extends Controller
{
    public function readiness(Request $request): JsonResponse
    {
        $expected = trim((string) config('operations.readiness_token'));
        $provided = trim((string) $request->header('X-Operations-Token'));
        abort_unless($expected !== '' && $provided !== '' && hash_equals($expected, $provided), 404);

        $checks = [
            'database' => fn () => DB::select('select 1'),
            'cache' => function () {
                $key = 'ops:readiness:'.bin2hex(random_bytes(6));
                Cache::put($key, 'ok', 10);
                $ok = Cache::pull($key) === 'ok';
                if (! $ok) {
                    throw new \RuntimeException('cache unavailable');
                }
            },
            'storage' => function () {
                $path = 'health/.probe-'.bin2hex(random_bytes(6));
                if (! Storage::disk('local')->put($path, 'ok')) {
                    throw new \RuntimeException('storage unavailable');
                }
                Storage::disk('local')->delete($path);
            },
        ];

        $result = [];
        foreach ($checks as $name => $check) {
            try {
                $check();
                $result[$name] = 'ok';
            } catch (Throwable) {
                $result[$name] = 'failed';
            }
        }

        $ready = ! in_array('failed', $result, true);

        return response()->json([
            'status' => $ready ? 'ready' : 'not_ready',
            'checks' => $result,
            'request_id' => $request->attributes->get('request_id'),
        ], $ready ? 200 : 503);
    }
}
