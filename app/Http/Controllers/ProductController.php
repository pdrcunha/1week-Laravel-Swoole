<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Redis;
use function Co\go;

class ProductController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/products",
     *     summary="Exibe uma lista de todos os produtos da empresa",
     *     tags={"Products"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Lista de produtos",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Product"))
     *     )
     * )
     */
    public function index(Request $request)
    {
        try {
            $companyId = $request->user->company_id;
            $cacheKey = "products:company:{$companyId}";

            $products = Cache::remember($cacheKey, 300, function () use ($companyId) {
                return Product::where('company_id', $companyId)->get();
            });

            $isCached = Cache::has($cacheKey);

            return response()->json([
                'cache' => $isCached,
                'data' => $products
            ]);
        } catch (\Throwable $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/products",
     *     summary="Armazena um novo produto",
     *     tags={"Products"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="price", type="number"),
     *             @OA\Property(property="qty", type="number"),
     *             @OA\Property(property="qty_min", type="number")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Retorna o produto criado",
     *         @OA\JsonContent(ref="#/components/schemas/Product")
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
                'price' => 'required|numeric',
                'qty' => 'required|numeric',
                'qty_min' => 'required|numeric'
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $data = $validator->validated();
            $data['company_id'] = $request->user->company_id;
            $product = Product::create($data);

            $this->invalidateCache($data['company_id']);

            go(function () use ($product) {
                Redis::rpush('product_queue', json_encode([
                    'name' => $product->name,
                    'quantity' => $product->qty,
                    'minimum_quantity' => $product->qty_min,
                    'company_id' => $product->company_id
                ]));
            });

            return response()->json($product, 201);
        } catch (\Throwable $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/products/{id}",
     *     summary="Exibe um produto específico",
     *     tags={"Products"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Retorna os dados do produto",
     *         @OA\JsonContent(ref="#/components/schemas/Product")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Produto não encontrado",
     *         @OA\JsonContent(type="object", @OA\Property(property="error", type="string"))
     *     )
     * )
     */

    public function show(Request $request, $id)
    {
        try {
            $companyId = $request->user->company_id;
            $cacheKey = "products:company:{$companyId}:product:{$id}";

            $product = Cache::remember($cacheKey, 300, function () use ($companyId, $id) {
                return Product::where('id', $id)->where('company_id', $companyId)->first();
            });

            $isCached = Cache::has($cacheKey);

            if (!$product) {
                return response()->json(['error' => 'Produto não encontrado'], 404);
            }

            return response()->json([
                'cache' => $isCached,
                'data' => $product
            ]);
        } catch (\Throwable $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/v1/products/{id}",
     *     summary="Atualiza um produto específico",
     *     tags={"Products"},
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
     *             @OA\Property(property="price", type="number"),
     *             @OA\Property(property="qty", type="number"),
     *             @OA\Property(property="qty_min", type="number")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Produto atualizado com sucesso",
     *         @OA\JsonContent(ref="#/components/schemas/Product")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erros de validação",
     *         @OA\JsonContent(type="object", @OA\Property(property="errors", type="object"))
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Produto não encontrado",
     *         @OA\JsonContent(type="object", @OA\Property(property="error", type="string"))
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        try {
            $companyId = $request->user->company_id;
            $product = Product::where('id', $id)->where('company_id', $companyId)->firstOrFail();

            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|required|string|max:255',
                'price' => 'sometimes|required|numeric',
                'qty' => 'required|numeric',
                'qty_min' => 'required|numeric'
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $product->update($validator->validated());
            $this->invalidateCache($companyId, $id);
            go(function () use ($product) {
                Redis::rpush('product_queue', json_encode([
                    'name' => $product->name,
                    'quantity' => $product->qty,
                    'minimum_quantity' => $product->qty_min,
                    'company_id' => $product->company_id
                ]));
            });

            return response()->json($product, 200);
        } catch (\Throwable $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/products/{id}",
     *     summary="Remove um produto específico",
     *     tags={"Products"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Produto removido com sucesso"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Produto não encontrado",
     *         @OA\JsonContent(type="object", @OA\Property(property="error", type="string"))
     *     )
     * )
     */
    public function destroy(Request $request, $id)
    {
        try {
            $companyId = $request->user->company_id;
            $product = Product::where('id', $id)->where('company_id', $companyId)->firstOrFail();
            $product->delete();

            $this->invalidateCache($companyId, $id);

            return response()->json(null, 204);
        } catch (\Throwable $e) {
            return $this->handleException($e);
        }
    }

    private function invalidateCache($companyId, $productId = null)
    {
        Cache::forget("products:company:{$companyId}");

        if ($productId) {
            Cache::forget("products:company:{$companyId}:product:{$productId}");
        }
    }
}
