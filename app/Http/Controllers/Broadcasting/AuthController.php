<?php

namespace App\Http\Controllers\Broadcasting;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use Pusher\Pusher;

class AuthController extends Controller
{
    public function authenticate(Request $request): JsonResponse
    {
        $token = $request->bearerToken();
        $channelName = $request->input('channel_name');
        $socketId = $request->input('socket_id');

        if (!$token) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $accessToken = PersonalAccessToken::findToken($token);

        if (!$accessToken) {
            return response()->json(['message' => 'Invalid token'], 401);
        }

        $usuario = $accessToken->tokenable;

        if (!preg_match('/^private-usuario\.(\d+)$/', $channelName, $matches)) {
            return response()->json(['message' => 'Invalid channel format'], 400);
        }

        $canalId = $matches[1];

        if ((int)$usuario->id_usuario !== (int)$canalId) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            $pusher = new Pusher(
                config('broadcasting.connections.pusher.key'),
                config('broadcasting.connections.pusher.secret'),
                config('broadcasting.connections.pusher.app_id'),
                [
                    'cluster' => config('broadcasting.connections.pusher.options.cluster'),
                    'useTLS' => true
                ]
            );

            $auth = $pusher->authorizeChannel($channelName, $socketId);

            return response()->json(json_decode($auth));
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Authorization failed',
            ], 500);
        }
    }
}
