<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;

/**
 * @OA\Info(
 *      version="1.0.0",
 *      title="1week teste laravel",
 *      description="Api laravel com swoole, aprendizado de uma semana",
 *      @OA\Contact(
 *          email="pedro.2112@hotmail.com.br"
 *      )
 * ),
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 */

abstract class Controller
{
    protected function handleException(\Throwable $e)
    {
        Log::error("Exception in file {$e->getFile()} on line {$e->getLine()}: {$e->getMessage()}");
        return response()->json(['error' => 'An unexpected error occurred. Please try again later.'], 500);
    }
}
