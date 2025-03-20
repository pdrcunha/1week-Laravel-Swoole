<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
  /**
   * @OA\Get(
   *     path="/api/v1/users",
   *     summary="Exibe uma lista de todos os usuários da empresa",
   *     tags={"Users"},
   *     security={{"bearerAuth":{}}},
   *     @OA\Response(
   *         response=200,
   *         description="Lista de usuários",
   *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/User"))
   *     )
   * )
   */
  public function index(Request $request)
  {
    try {
      $companyId = $request->user->company_id;
      return response()->json(User::where('company_id', $companyId)->get());
    } catch (\Throwable $e) {
      return $this->handleException($e);
    }
  }

  /**
   * @OA\Post(
   *     path="/api/v1/users",
   *     summary="Armazena um novo usuário",
   *     tags={"Users"},
   *     security={{"bearerAuth":{}}},
   *     @OA\RequestBody(
   *         required=true,
   *         @OA\JsonContent(
   *             @OA\Property(property="name", type="string"),
   *             @OA\Property(property="email", type="string"),
   *             @OA\Property(property="password", type="string")
   *         )
   *     ),
   *     @OA\Response(
   *         response=201,
   *         description="Usuário criado com sucesso",
   *         @OA\JsonContent(ref="#/components/schemas/User")
   *     ),
   *     @OA\Response(
   *         response=422,
   *         description="Erros de validação",
   *         @OA\JsonContent(type="object", @OA\Property(property="errors", type="object"))
   *     )
   * )
   */
  public function store(Request $request)
  {
    try {
      $validator = Validator::make($request->all(), [
        'name' => 'required|string|max:255',
        'email' => 'required|email|max:255|unique:users',
        'password' => 'required|string|min:8',
      ]);

      if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
      }

      $validatedData = $validator->validated();

      $user = User::create([
        'name' => $validatedData['name'],
        'email' => $validatedData['email'],
        'password' => Hash::make($validatedData['password']),
        'company_id' => $request->user->company_id,
      ]);

      return response()->json($user, 201);
    } catch (\Throwable $e) {
      return $this->handleException($e);
    }
  }

  /**
   * @OA\Get(
   *     path="/api/v1/users/{id}",
   *     summary="Exibe um usuário específico",
   *     tags={"Users"},
   *     security={{"bearerAuth":{}}},
   *     @OA\Parameter(
   *         name="id",
   *         in="path",
   *         required=true,
   *         @OA\Schema(type="integer")
   *     ),
   *     @OA\Response(
   *         response=200,
   *         description="Dados do usuário",
   *         @OA\JsonContent(ref="#/components/schemas/User")
   *     ),
   *     @OA\Response(
   *         response=404,
   *         description="Usuário não encontrado",
   *         @OA\JsonContent(type="object", @OA\Property(property="error", type="string"))
   *     )
   * )
   */
  public function show(Request $request, $id)
  {
    try {
      $companyId = $request->user->company_id;
      $user = User::where('id', $id)->where('company_id', $companyId)->first();

      if (!$user) {
        return response()->json(['error' => 'User not found'], 404);
      }

      return response()->json($user);
    } catch (\Throwable $e) {
      return $this->handleException($e);
    }
  }

  /**
   * @OA\Put(
   *     path="/api/v1/users/{id}",
   *     summary="Atualiza um usuário específico",
   *     tags={"Users"},
   *     security={{"bearerAuth":{}}},
   *     @OA\Parameter(
   *         name="id",
   *         in="path",
   *         required=true,
   *         @OA\Schema(type="integer")
   *     ),
   *     @OA\RequestBody(
   *         required=true,
   *         @OA\JsonContent(
   *             @OA\Property(property="name", type="string"),
   *             @OA\Property(property="email", type="string"),
   *             @OA\Property(property="password", type="string")
   *         )
   *     ),
   *     @OA\Response(
   *         response=200,
   *         description="Usuário atualizado com sucesso",
   *         @OA\JsonContent(ref="#/components/schemas/User")
   *     ),
   *     @OA\Response(
   *         response=404,
   *         description="Usuário não encontrado",
   *         @OA\JsonContent(type="object", @OA\Property(property="error", type="string"))
   *     ),
   *     @OA\Response(
   *         response=422,
   *         description="Erros de validação",
   *         @OA\JsonContent(type="object", @OA\Property(property="errors", type="object"))
   *     )
   * )
   */
  public function update(Request $request, $id)
  {
    try {
      $companyId = $request->user->company_id;
      $user = User::where('id', $id)->where('company_id', $companyId)->first();

      if (!$user) {
        return response()->json(['error' => 'User not found'], 404);
      }

      $validator = Validator::make($request->all(), [
        'name' => 'sometimes|required|string|max:255',
        'email' => 'sometimes|required|string|email|max:255|unique:users,email,' . $id,
        'password' => 'sometimes|required|string|min:8',
      ]);

      if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
      }

      $validatedData = $validator->validated();

      if (isset($validatedData['password'])) {
        $validatedData['password'] = Hash::make($validatedData['password']);
      }

      $user->update($validatedData);

      return response()->json($user);
    } catch (\Throwable $e) {
      return $this->handleException($e);
    }
  }

  /**
   * @OA\Delete(
   *     path="/api/v1/users/{id}",
   *     summary="Remove um usuário específico",
   *     tags={"Users"},
   *     security={{"bearerAuth":{}}},
   *     @OA\Parameter(
   *         name="id",
   *         in="path",
   *         required=true,
   *         @OA\Schema(type="integer")
   *     ),
   *     @OA\Response(
   *         response=200,
   *         description="Usuário removido com sucesso",
   *         @OA\JsonContent(type="object", @OA\Property(property="message", type="string"))
   *     ),
   *     @OA\Response(
   *         response=404,
   *         description="Usuário não encontrado",
   *         @OA\JsonContent(type="object", @OA\Property(property="error", type="string"))
   *     )
   * )
   */
  public function destroy(Request $request, $id)
  {
    try {
      $companyId = $request->user->company_id;
      $user = User::where('id', $id)->where('company_id', $companyId)->first();

      if (!$user) {
        return response()->json(['error' => 'User not found'], 404);
      }

      $user->delete();

      return response()->json(['message' => 'User deleted successfully']);
    } catch (\Throwable $e) {
      return $this->handleException($e);
    }
  }
}
