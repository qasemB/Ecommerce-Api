<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RoleController extends Controller
{
    /**
     * @OA\Get(
     *  path="/api/admin/roles",
     *  summary="Get all Roles",
     *  description="get all roles",
     *  operationId="getRoles",
     *  tags={"Users"},
     *  security={ {"bearer_token": {} }},
     *  @OA\Response(
     *    response=200,
     *    description="success",
     *    @OA\JsonContent(
     *       @OA\Property(property="data", type="string", example="[{}, {}]")
     *     )
     *  )
     * )
     * @param Request $request
     * @return JsonResponse
     */
    public function index()
    {
        $roles = Role::where('title', '!=', 'admin')->with('permissions')->get(['id', 'title', 'description']);
        $roleCount = sizeof($roles);
        return response()->json([
            'data' => $roles,
            'message' => $roleCount > 0 ? "تعداد $roleCount نقش دریافت شد" : "فعلا نقشی ایجاد نشده است"
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
        /**
     * @OA\Post(
     * path="/api/admin/roles",
     * summary="Add Role",
     * description="Store one Role",
     * operationId="addRole",
     * tags={"Users"},
     * security={ {"bearer_token": {} }},
     *  @OA\RequestBody(
     *    required=true,
     *    description="Add one Role",
     *  @OA\MediaType(
     *    mediaType="application/json",
     *    @OA\Schema(
     *       required={"title", "permissions_id"},
     *      @OA\Property(property="title", type="string",  example="role_name_1"),
     *      @OA\Property(property="description", type="string",  example="example description"),
     *      @OA\Property(property="permissions_id", type="object",  example="[2,3]"),
     *    )
     *   ),
     * ),
     * @OA\Response(
     *    response=201,
     *    description="success",
     *    @OA\JsonContent(
     *       @OA\Property(property="data", type="string", example="[{}, {}]"),
     *        )
     *     )
     * )
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all() , [
            'title' => 'required|regex:/^[a-zA-z0-9\-0-9ء-ئ., ؟!:.،\n آابپتثجچحخدذرزژسشصضطظعغفقکگلمنوهیئ\s]+$/',
            'description' => 'nullable',
            'permissions_id.*' => 'exists:permissions,id',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 202);
        }

        $role = new Role();
        $role->title = $request['title'];
        $role->description = $request['description'];

        $role->save();

        $role->permissions()->attach($request['permissions_id']);

        $newRole = Role::with('permissions')->where('id', $role->id)->first(['id', 'title', 'description']);

        return response()->json([
            'data' => $newRole,
            'message' => 'عملیات با موفقیت انجام شد'
        ], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
