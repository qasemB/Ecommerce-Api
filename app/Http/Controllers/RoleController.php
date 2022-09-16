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
            'title' => 'required|unique:roles,title|regex:/^[a-zA-z0-9\-0-9ء-ئ., ؟!:.،\n آابپتثجچحخدذرزژسشصضطظعغفقکگلمنوهیئ\s]+$/',
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
    /**
     * @OA\Get(
     *      path="/api/admin/roles/{id}",
     *      summary="Get one role",
     *      description="Get one role with id",
     *      operationId="oneRole",
     *      tags={"Users"},
     *      security={ {"bearer_token": {} }},
     *  @OA\Parameter(
     *      in="path",
     *      name="id",
     *      required=true,
     *      @OA\Schema(type="number")
     *  ),
     *  @OA\Response(
     *    response=200,
     *    description="success",
     *    @OA\JsonContent(
     *      @OA\Property(property="data", type="string", example="{}")
     *   )
     *  )
     * )
     */
    public function show(int $id)
    {
        $role = Role::with('permissions')->find($id);
        return response()->json([
            'data' => $role,
            'message' => 'دریافت  با موفقیت انجام شد'
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    /**
     * @OA\Put(
     * path="/api/admin/roles/{id}",
     * summary="Edit role",
     * description="Edit one role",
     * operationId="editRole",
     * tags={"Users"},
     * security={ {"bearer_token": {} }},
     *  @OA\Parameter(
     *      in="path",
     *      name="id",
     *      required=true,
     *      @OA\Schema(type="string")
     *  ),
     *  @OA\RequestBody(
     *      required=true,
     *      description="edit category",
     *      @OA\MediaType(
     *          mediaType="application/json",
     *          @OA\Schema(
     *              @OA\Property(property="title", type="string"),
     *              @OA\Property(property="description", type="string"),
     *          ),
     *        example={
     *          "title" : "edited role title",
     *          "description" : "description of role edited",
     *        }
     *      ),
     * ),
     * @OA\Response(
     *    response=200,
     *    description="success",
     *    @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="عملیت با موفقیت انجام شد"),
     *        )
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all() , [
            'title' => "required|unique:permissions,title,$id,id|regex:/^[a-zA-z0-9\-0-9ء-ئ., ؟!:.،\n آابپتثجچحخدذرزژسشصضطظعغفقکگلمنوهیئ\s]+$/" ,
            'description' => 'nullable|regex:/^[a-zA-z0-9\-0-9ء-ئ., ؟!:.،\n آابپتثجچحخدذرزژسشصضطظعغفقکگلمنوهیئ\s]+$/' ,
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 202);
        }

        $role = Role::with('permissions')->find($id);
        $role->title = $request['title'];
        $role->description = $request['description'];
        $role->save();
        return response()->json([
            'data' => $role,
            'message' => 'ویرایش با موفقیت انجام شد'
        ], 200);
    }


    /**
     * @OA\Put(
     * path="/api/admin/roles/{id}/permissions",
     * summary="Edit role permissions",
     * description="Edit one role permissions",
     * operationId="editRolePermissions",
     * tags={"Users"},
     * security={ {"bearer_token": {} }},
     *  @OA\Parameter(
     *      in="path",
     *      name="id",
     *      required=true,
     *      @OA\Schema(type="string")
     *  ),
     *  @OA\RequestBody(
     *      required=true,
     *      description="edit category",
     *      @OA\MediaType(
     *          mediaType="application/json",
     *          @OA\Schema(
     *              @OA\Property(property="permissions_id", type="object", example="[2,3]"),
     *          ),
     *      ),
     * ),
     * @OA\Response(
     *    response=200,
     *    description="success",
     *    @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="تغییر دسترسی با موفقیت انجام شد"),
     *        )
     *     )
     * )
     */
    public function updatePermissions(Request $request, $id)
    {
        $validator = Validator::make($request->all() , [
            'permissions_id' => 'required',
            'permissions_id.*' => 'exists:permissions,id',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 202);
        }

        $role = Role::find($id);
        $role->permissions()->sync($request['permissions_id'], true);
        $role->save();

        $role = Role::with('permissions')->find($id);
        return response()->json([
            'data' => $role,
            'message' => 'تغییر دسترسی با موفقیت انجام شد'
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    /**
     * @OA\Delete(
     * path="/api/admin/roles/{id}",
     * summary="Delete role",
     * description="Delete one role",
     * operationId="deleteRole",
     * tags={"Users"},
     * security={ {"bearer_token": {} }},
     *  @OA\Parameter(
     *      in="path",
     *      name="id",
     *      required=true,
     *      @OA\Schema(type="string")
     *  ),
     * @OA\Response(
     *    response=200,
     *    description="success",
     *    @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="حذف با موفقیت انجام شد"),
     *        )
     *     )
     * )
     */
    public function destroy($id)
    {
        Role::destroy($id);
        return response()->json([
            'message' => 'نقش با موفقیت حذف شد'
        ] , 200);
    }
}
