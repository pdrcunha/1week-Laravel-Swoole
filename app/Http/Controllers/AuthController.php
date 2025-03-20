<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthController extends Controller
{

  /**
   * @OA\Post(
   *    path="/api/v1/auth/",
   *    summary="Login",
   *    description="Login",
   *    tags={"Auth"},
   *    @OA\RequestBody(
   *      required=true,
   *      @OA\JsonContent(
   *        required={"email","password"},
   *        @OA\Property(property="email", type="string", format="email", example="admin@user.com"),
   *        @OA\Property(property="password", type="string", format="password", example="password")
   *      )
   *    ),
   *    @OA\Response(
   *      response=200,
   *      description="Success",
   *      @OA\JsonContent(
   *        @OA\Property(property="user", ref="#/components/schemas/User"),
   *        @OA\Property(property="token", type="string"),
   *      )
   *    ),
   *    @OA\Response(
   *      response=401,
   *      description="Invalid credentials",
   *      @OA\JsonContent(
   *        @OA\Property(property="error", type="string")
   *      )
   *    ),
   *    @OA\Response(
   *      response=500,
   *      description="Could not create token",
   *      @OA\JsonContent(
   *        @OA\Property(property="error", type="string")
   *      )
   *    )
   * )
   */
  public function login(Request $request)
  {
    $credentials = $request->validate([
      'email' => 'required|email',
      'password' => 'required|string',
    ]);

    try {
      if (!$token = JWTAuth::attempt($credentials)) {
        return response()->json(['error' => 'Invalid credentials'], 401);
      }
    } catch (JWTException $e) {
      return response()->json(['error' => 'Could not create token'], 500);
    }

    return response()->json([
      'user' => [
        'id' => JWTAuth::user()->id,
        'name' => JWTAuth::user()->name,
        'email' => JWTAuth::user()->email
      ],
      'token' => $token,
    ]);
  }

  /**
   * @OA\Get(
   *    path="/api/v1/auth/me",
   *    summary="Get authenticated user",
   *    description="Get authenticated user",
   *    tags={"Auth"},
   *    security={{"bearerAuth":{}}},
   *    @OA\Response(
   *      response=200,
   *      description="Success",
   *      @OA\JsonContent(ref="#/components/schemas/User")
   *    ),
   *    @OA\Response(
   *      response=401,
   *      description="Unauthenticated",
   *      @OA\JsonContent(
   *        @OA\Property(property="error", type="string")
   *      )
   *    )
   * )
   */
  public function me(Request $request)
  {
    return response()->json(User::find($request->user->id));
  }

  /**
   * @OA\Put(
   *    path="/api/v1/auth/me",
   *    summary="Update authenticated user",
   *    description="Update authenticated user",
   *    tags={"Auth"},
   *    security={{"bearerAuth":{}}},
   *    @OA\RequestBody(
   *      required=true,
   *      @OA\JsonContent(
   *        @OA\Property(property="name", type="string", maxLength=255),
   *        @OA\Property(property="email", type="string", format="email", maxLength=255),
   *        @OA\Property(property="password", type="string", format="password", nullable=true),
   *        @OA\Property(property="password_confirmation", type="string", format="password", nullable=true)
   *      )
   *    ),
   *    @OA\Response(
   *      response=200,
   *      description="Profile updated successfully",
   *      @OA\JsonContent(
   *        @OA\Property(property="message", type="string"),
   *        @OA\Property(property="user", ref="#/components/schemas/User")
   *      )
   *    ),
   *    @OA\Response(
   *      response=422,
   *      description="Validation error",
   *      @OA\JsonContent(
   *        @OA\Property(property="errors", type="object")
   *      )
   *    ),
   *    @OA\Response(
   *      response=401,
   *      description="Unauthenticated",
   *      @OA\JsonContent(
   *        @OA\Property(property="error", type="string")
   *      )
   *    )
   * )
   */
  public function updateMe(Request $request)
  {
    $user = $request->user;

    $validator = Validator::make($request->all(), [
      'name' => 'required|string|max:255',
      'email' => 'required|email|max:255|unique:users,email,' . $user->id,
      'password' => 'nullable|string|min:8|confirmed',
    ]);

    if ($validator->fails()) {
      return response()->json(['errors' => $validator->errors()], 422);
    }

    $user->name = $request->input('name');
    $user->email = $request->input('email');
    if ($request->filled('password')) {
      $user->password = bcrypt($request->input('password'));
    }
    $user->save();

    return response()->json([
      'message' => 'Perfil atualizado com sucesso!',
      'user' => $user
    ]);
  }
}
