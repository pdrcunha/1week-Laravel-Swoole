<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;

class CompanyController extends Controller
{
  /**
   * @OA\Get(
   *     path="/api/v1/companies",
   *     summary="Exibe uma lista de todas as empresas",
   *     tags={"Companies"},
   *     security={{"bearerAuth":{}}},
   *     @OA\Response(
   *         response=200,
   *         description="Lista de empresas",
   *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Company"))
   *     )
   * )
   */
  public function index()
  {
    $cacheKey = "companies:all";
    $companies = Cache::remember($cacheKey, 300, function () {
        return Company::all();
    });
    return response()->json($companies);
  }

  /**
   * @OA\Get(
   *     path="/api/v1/companies/{id}",
   *     summary="Exibe uma empresa específica",
   *     tags={"Companies"},
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
   *         @OA\JsonContent(ref="#/components/schemas/Company")
   *     ),
   *     @OA\Response(
   *         response=404,
   *         description="Empresa não encontrada",
   *         @OA\JsonContent(type="object", @OA\Property(property="error", type="string"))
   *     )
   * )
   */
  public function show(Request $req)
  {
    $companyId = $req->user->company_id;
    $cacheKey = "companies:company:{$companyId}";
    $company = Cache::remember($cacheKey, 300, function () use ($companyId) {
        return Company::find($companyId);
    });

    if (!$company) {
      return response()->json(['error' => 'Company not found'], 404);
    }
    return response()->json($company);
  }

  /**
   * @OA\Put(
   *     path="/api/v1/companies/{id}",
   *     summary="Atualiza uma empresa específica",
   *     tags={"Companies"},
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
   *             @OA\Property(property="cnpj", type="string")
   *         )
   *     ),
   *     @OA\Response(
   *         response=200,
   *         description="Empresa atualizada com sucesso",
   *         @OA\JsonContent(ref="#/components/schemas/Company")
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
    $company = Company::find($companyId);
    if (!$company) {
      return response()->json(['error' => 'Company not found'], 404);
    }

    $validator = Validator::make($req->all(), [
      'name' => 'required|string|max:255',
      'email' => 'required|string|max:255',
      'cnpj' => 'required|string|max:18|unique:companies,cnpj,' . $company->id,
    ]);

    if ($validator->fails()) {
      return response()->json(['errors' => $validator->errors()], 422);
    }

    $company->update($validator->validated());
    $this->invalidateCache($companyId);
    return response()->json($company);
  }

  /**
   * @OA\Delete(
   *     path="/api/v1/companies/{id}",
   *     summary="Remove uma empresa específica",
   *     tags={"Companies"},
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
  public function destroy(Request $req)
  {
    $companyId = $req->user->company_id;
    $company = Company::find($companyId);
    if (!$company) {
      return response()->json(['error' => 'Company not found'], 404);
    }
    $company->delete();
    $this->invalidateCache($companyId);
    return response()->json(['message' => 'Company deleted successfully']);
  }

  private function invalidateCache($companyId)
  {
    Cache::forget("companies:all");
    Cache::forget("companies:company:{$companyId}");
  }
}