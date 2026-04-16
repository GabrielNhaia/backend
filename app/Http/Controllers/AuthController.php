<?php
namespace App\Http\Controllers;

use App\Http\Requests\RegisterUsuarioRequest;
use App\Models\Usuario;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function register(RegisterUsuarioRequest $request): JsonResponse
    {
        $senha = $request->senha ?? $request->password;

        $usuario = Usuario::create([
            'nome'            => $request->nome,
            'email'           => $request->email,
            'senha'           => Hash::make($senha),
            'telefone'        => $request->telefone,
            'data_nascimento' => $request->data_nascimento,
            'status'          => 'ativo',
            'data_expiracao'  => Carbon::today()->addDays(7),
        ]);

        $token = Auth::login($usuario);

        return response()->json([
            'usuario' => $usuario,
            'token' => $token,
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $request->merge([
            'senha' => $request->input('senha', $request->input('password')),
        ]);

        $validator = Validator::make($request->all(), [
            'email'    => 'required|email',
            'senha'    => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $usuario = Usuario::where('email', $request->email)->first();

        if (!$usuario || !Hash::check($request->senha, $usuario->senha)) {
            return response()->json(['message' => 'Invalid credentials.'], 401);
        }

        $token = Auth::login($usuario);

        return response()->json([
            'usuario' => $usuario,
            'token' => $token,
        ]);
    }

    public function me(): JsonResponse
    {
        return response()->json(Auth::user());
    }

    public function logout(): JsonResponse
    {
        Auth::logout();

        return response()->json(['message' => 'Successfully logged out.']);
    }
}
