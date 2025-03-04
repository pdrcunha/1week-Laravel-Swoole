<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthController extends Controller
{
  public function login(Request $request)
  {
    $credentials = $request->validate([
      'email' => 'required|string|email',
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
      'user' => Auth::user(),
      'token' => $token,
    ]);
  }

  public function me()
  {
    return response()->json(Auth::user());
  }

  public function updateMe(Request $request)
  {
    $user = User::find($request->user->id);

    $request->validate([
      'name' => 'required|string|max:255',
      'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
    ]);

    $user->name = $request->input('name');
    $user->email = $request->input('email');
    $user->save();

    return response()->json($user);
  }
}
