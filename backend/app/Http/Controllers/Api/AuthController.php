<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\LoginRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(LoginRequest $request): JsonResponse
    {
        // Mensagem genérica de propósito: nunca revela se o e-mail existe (regra 15 de autenticacao.md).
        if (! Auth::attempt($request->validated())) {
            return response()->json(['message' => 'Credenciais inválidas.'], 422);
        }

        $user = $request->user();

        $token = $user->createToken(config('api.token_name'));

        return response()->json([
            'message' => 'Login realizado com sucesso.',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ],
                'token' => $token->accessToken,
                'expires_at' => $token->token?->expires_at,
            ],
        ], 200);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->token()->revoke();

        return response()->json(['message' => 'Logout realizado com sucesso.'], 200);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ],
            ],
        ], 200);
    }
}
