<?php

namespace App\Http\Controllers\AdminManager;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;

class CompanyManagerController extends Controller
{
  const adminCompanyId = 1;
  /**
   * @OA\Get(
   *     path="/api/v1/companies-admin",
   *     summary="Exibe uma lista de todas as empresas",
   *     tags={"Companies - Admin"},
   *     security={{"bearerAuth":{}}},
   *     @OA\Response(
   *         response=200,
   *         description="Lista de empresas",
   *         @OA\JsonContent(type="array", @OA\Items(
   *             @OA\Property(property="id", type="integer"),
   *             @OA\Property(property="name", type="string"),
   *             @OA\Property(property="address", type="string"),
   *             @OA\Property(property="cnpj", type="string"),
   *            @OA\Property(
   *                     property="users",
   *                     type="array",
   *                     @OA\Items(ref="#/components/schemas/User")
   *                 )
   *         ))
   *     )
   * )
   */
  public function index(Request $req)
  {
    $companyId = $req->user->company_id;
    if ($companyId != self::adminCompanyId) {
      return response()->json(['error' => 'Not permission'], 401);
    }

    $cacheKey = "companies-admin:all";
    $companies = Cache::remember($cacheKey, 300, function () {
        return Company::with('users')->get();
    });

    return response()->json($companies);
  }

  /**
   * @OA\Post(
   *     path="/api/v1/companies-admin",
   *     summary="Armazena uma nova empresa",
   *     tags={"Companies - Admin"},
   *     security={{"bearerAuth":{}}},
   *     @OA\RequestBody(
   *         required=true,
   *         @OA\JsonContent(
   *             @OA\Property(property="name", type="string"),
   *             @OA\Property(property="email", type="string"),
   *             @OA\Property(property="cnpj", type="string")
   *         )
   *     ),
   *     @OA\Response(
   *         response=201,
   *         description="Empresa criada com sucesso",
   *         @OA\JsonContent(
   *             @OA\Property(property="id", type="integer"),
   *             @OA\Property(property="name", type="string"),
   *             @OA\Property(property="address", type="string"),
   *             @OA\Property(property="cnpj", type="string")
   *         )
   *     ),
   *     @OA\Response(
   *         response=422,
   *         description="Erros de validação",
   *         @OA\JsonContent(type="object", @OA\Property(property="errors", type="object"))
   *     )
   * )
   */
  public function store(Request $req)
  {
    $companyId = $req->user->company_id;
    if ($companyId != self::adminCompanyId) {
      return response()->json(['error' => 'Not permission'], 401);
    }

    $validator = Validator::make($req->all(), [
      'id' => 'required|integer',
      'name' => 'required|string|max:255',
      'email' => 'required|string|max:255',
      'cnpj' => 'required|string|max:18|unique:companies,cnpj,' . $req->all()['id']
    ]);

    if ($validator->fails()) {
      return response()->json(['errors' => $validator->errors()], 422);
    }

    $company = Company::create($validator->validated());

    User::create([
      'name' => 'Admin',
      'email' => 'admin_' . $company->cnpj . '@example.com',
      'password' => bcrypt('password'),
      'company_id' => $company->id,
      'role' => 'admin',
    ]);

    $this->invalidateCache($company->id);

    return response()->json($company, 201);
  }

  /**
   * @OA\Get(
   *     path="/api/v1/companies-admin/{id}",
   *     summary="Exibe uma empresa específica",
   *     tags={"Companies - Admin"},
   *     security={{"bearerAuth":{}}},
   *     @OA\Parameter(
   *         name="id",
   *         in="path",
   *         required=true,
   *         @OA\Schema(type="integer")
   *     ),
   *     @OA\Response(
   *         response=200,
   *         description="Dados da empresa",
   *         @OA\JsonContent(
   *             @OA\Property(property="id", type="integer"),
   *             @OA\Property(property="name", type="string"),
   *             @OA\Property(property="address", type="string"),
   *             @OA\Property(property="cnpj", type="string")
   *         )
   *     ),
   *     @OA\Response(
   *         response=404,
   *         description="Empresa não encontrada",
   *         @OA\JsonContent(type="object", @OA\Property(property="error", type="string"))
   *     )
   * )
   */
  public function show(Request $req, $id)
  {
    $companyId = $req->user->company_id;
    if ($companyId != self::adminCompanyId) {
      return response()->json(['error' => 'Not permission'], 401);
    }

    $cacheKey = "companies-admin:company:{$id}";
    $company = Cache::remember($cacheKey, 300, function () use ($id) {
        return Company::find($id);
    });

    if (!$company) {
      return response()->json(['error' => 'Company not found'], 404);
    }
    return response()->json($company);
  }

  /**
   * @OA\Put(
   *     path="/api/v1/companies-admin/{id}",
   *     summary="Atualiza uma empresa específica",
   *     tags={"Companies - Admin"},
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
   *             @OA\Property(property="address", type="string"),
   *             @OA\Property(property="cnpj", type="string")
   *         )
   *     ),
   *     @OA\Response(
   *         response=200,
   *         description="Empresa atualizada com sucesso",
   *         @OA\JsonContent(
   *             @OA\Property(property="id", type="integer"),
   *             @OA\Property(property="name", type="string"),
   *             @OA\Property(property="email", type="string"),
   *             @OA\Property(property="cnpj", type="string")
   *         )
   *     ),
   *     @OA\Response(
   *         response=422,
   *         description="Erros de validação",
   *         @OA\JsonContent(type="object", @OA\Property(property="errors", type="object"))
   *     ),
   *     @OA\Response(
   *         response=404,
   *         description="Empresa não encontrada",
   *         @OA\JsonContent(type="object", @OA\Property(property="error", type="string"))
   *     )
   * )
   */
  public function update(Request $req)
  {
    $companyId = $req->user->company_id;
    if ($companyId != self::adminCompanyId) {
      return response()->json(['error' => 'Not permission'], 401);
    }

    $data = $req->all();
    $company = Company::find($data['id']);
    if (!$company) {
      return response()->json(['error' => 'Company not found'], 404);
    }

    $validator = Validator::make($req->all(), [
      'id' => 'required|integer',
      'name' => 'required|string|max:255',
      'email' => 'required|string|max:255',
      'cnpj' => 'required|string|max:18|unique:companies,cnpj,' . $req->all()['id']
    ]);

    if ($validator->fails()) {
      return response()->json(['errors' => $validator->errors()], 422);
    }

    $company->update($validator->validated());
    $this->invalidateCache($data['id']);
    return response()->json($company);
  }

  /**
   * @OA\Delete(
   *     path="/api/v1/companies-admin/{id}",
   *     summary="Remove uma empresa específica",
   *     tags={"Companies - Admin"},
   *     security={{"bearerAuth":{}}},
   *     @OA\Parameter(
   *         name="id",
   *         in="path",
   *         required=true,
   *         @OA\Schema(type="integer")
   *     ),
   *     @OA\Response(
   *         response=200,
   *         description="Empresa removida com sucesso",
   *         @OA\JsonContent(type="object", @OA\Property(property="message", type="string"))
   *     ),
   *     @OA\Response(
   *         response=404,
   *         description="Empresa não encontrada",
   *         @OA\JsonContent(type="object", @OA\Property(property="error", type="string"))
   *     )
   * )
   */
  public function destroy(Request $req, $id)
  {
    $companyId = $req->user->company_id;
    if ($companyId != self::adminCompanyId) {
      return response()->json(['error' => 'Not permission'], 401);
    }

    $company = Company::find($id);
    if (!$company) {
      return response()->json(['error' => 'Company not found'], 404);
    }
    $company->delete();
    $this->invalidateCache($id);
    return response()->json(['message' => 'Company deleted successfully']);
  }

  private function invalidateCache($companyId)
  {
    Cache::forget("companies-admin:all");
    Cache::forget("companies-admin:company:{$companyId}");
  }
}
