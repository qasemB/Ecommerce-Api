<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    /**
     * @OA\Get(
     *  path="/api/admin/permissions",
     *  summary="Get all permissions",
     *  description="Get all permissions",
     *  operationId="allPermissions",
     *  tags={"Users"},
     *  security={ {"bearer_token": {} }},
     *  @OA\Response(
     *    response=200,
     *    description="success",
     *    @OA\JsonContent(
     *       @OA\Property(property="data", type="string", example="[{...}, {...}]")
     *     )
     *  )
     * )
     * @return JsonResponse
     */
    public function index()
    {
        $permissions = Permission::all(['id', 'title', 'description', 'category']);
        $count = sizeof($permissions);
        return response()->json([
            'data' => $permissions,
            'message' => "تعداد $count مجوز یافت شد"
        ] , 200);
    }
}
