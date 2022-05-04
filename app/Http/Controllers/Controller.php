<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 *     @OA\Info(
 *         version="1.0",
 *         title="Admin panel API",
 *         description="Codeyad-React Learning-Ecommerce admin panel API"
 *     )
 *     @OA\SecurityScheme(
 *         securityScheme="bearer_token",
 *         type="http",
 *         scheme="bearer"
 *     )
 *     @OA\Tag(
 *         name="Auth",
 *         description="user authentication"
 *    )
 *    @OA\Tag(
 *         name="Categories",
 *         description="product categories"
 *    )
 */
class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
}
