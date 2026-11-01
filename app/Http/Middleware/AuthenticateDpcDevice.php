<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Device;

class AuthenticateDpcDevice
{
    /**
     * Handle an incoming request from the Android DPC client.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken() ?: $request->header('X-Device-Token');

        if (!$token) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Missing device authentication token (Bearer or X-Device-Token required).'
            ], 401);
        }

        $device = Device::where('device_token', $token)->first();

        if (!$device) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Invalid or unrecognized device token.'
            ], 401);
        }

        if ($device->state === 'DISABLED' || $device->state === 'DELETED') {
            return response()->json([
                'status'  => 'error',
                'message' => 'Device is disabled or removed from management.'
            ], 403);
        }

        // Bind device instance to request attributes for easy controller access
        $request->attributes->set('dpc_device', $device);

        return $next($request);
    }
}
