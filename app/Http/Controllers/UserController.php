<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
  public function index()
  {
    return response()->json(User::all());
  }

  public function store(Request $request)
  {
    $validatedData = $request->validate([
      'name' => 'required|string|max:255',
      'email' => 'required|string|email|max:255|unique:users',
      'password' => 'required|string|min:8',
    ]);

    $user = User::create([
      'name' => $validatedData['name'],
      'email' => $validatedData['email'],
      'password' => Hash::make($validatedData['password']),
    ]);

    return response()->json($user, 201);
  }

  public function show($id)
  {
    $user = User::find($id);
    if (!$user) {
      return response()->json(['error' => 'User not found'], 404);
    }
    return response()->json($user);
  }

  public function update(Request $request, $id)
  {
    $user = User::find($id);
    if (!$user) {
      return response()->json(['error' => 'User not found'], 404);
    }

    $validatedData = $request->validate([
      'name' => 'sometimes|required|string|max:255',
      'email' => 'sometimes|required|string|email|max:255|unique:users,email,' . $id,
      'password' => 'sometimes|required|string|min:8',
    ]);

    if (isset($validatedData['password'])) {
      $validatedData['password'] = Hash::make($validatedData['password']);
    }

    $user->update($validatedData);

    return response()->json($user);
  }

  public function destroy($id)
  {
    $user = User::find($id);
    if (!$user) {
      return response()->json(['error' => 'User not found'], 404);
    }

    $user->delete();

    return response()->json(['message' => 'User deleted successfully']);
  }
}
