<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules;

class RegisteredUserController extends Controller
{
    /**
     * @OA\Post(
     *    path="/register",
     *    tags={"Authentication"},
     *    summary="Register a new user",
     *
     *        @OA\RequestBody(
     *              required=true,
     *
     *              @OA\JsonContent(
     *                 type="object",
     *
     *                 @OA\Property(property="name", type="string", example="John Doe"),
     *                 @OA\Property(property="email", type="string", example="test@example.com"),
     *                 @OA\Property(property="password", type="string", example="password"),
     *                 @OA\Property(property="password_confirmation", type="string", example="password"),
     *              ),
     *      ),
     *
     *     @OA\Response(
     *           response=200,
     *           description="OK",
     *
     *     @OA\JsonContent(
     *                 type="object",
     *
     *                 @OA\Property(property="access_token", type="string", example="token"),
     *                 @OA\Property(property="token_type", type="string", example="Bearer"),
     *                 @OA\Property(property="user", type="object", example={"name":"John Doe","email":"test@example.com"}),
     *           )
     *       ),
     *
     *     @OA\Response(
     *          response=500,
     *          description="Server error",
     *
     *            @OA\JsonContent(
     *                 type="object",
     *
     *                 @OA\Property(property="message", type="string", example="Server error. Try again later."),
     *           )
     *      ),
     *  )
     *
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): JsonResponse|Response
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->string('password')),
            ]);

            event(new Registered($user));

            $token = $user->createToken('auth_token')->plainTextToken;

            Auth::login($user);

            return response()->json([
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => UserResource::make($user),
            ]);
        } catch (\Exception $e) {
            Log::error($e->getMessage(), ['error' => $e]);

            return response()->json([
                'message' => 'Server error. Try again later.',
            ], 500);
        }
    }
}
