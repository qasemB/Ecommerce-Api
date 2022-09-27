<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    /**
     * @OA\Get(
     *  path="/api/admin/users",
     *  summary="Get users",
     *  description="get all users or with pagination",
     *  operationId="getUsers",
     *  tags={"Users"},
     *  security={ {"bearer_token": {} }},
     *  @OA\Parameter(
     *      in="query",
     *      name="page",
     *      required=false,
     *      @OA\Schema(type="number")
     *  ),
     *  @OA\Parameter(
     *      in="query",
     *      name="count",
     *      required=false,
     *      @OA\Schema(type="number")
     *  ),
     *  @OA\Parameter(
     *      in="query",
     *      name="searchChar",
     *      required=false,
     *      @OA\Schema(type="string")
     *  ),
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
    public function index(Request $request)
    {
        $page = (int) $request->input("page");
        $searchChar = $request->input("searchChar") ?  $request->input("searchChar") : "";
        $validator = Validator::make(['searchChar'=>$searchChar] , [
            'searchChar' => 'nullable|regex:/^[a-zA-z0-9\-0-9ء-ئ., ؟!:.،\n آابپتثجچحخدذرزژسشصضطظعغفقکگلمنوهیئ\s]+$/',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 202);
        }
        if ($page) {
            $count = (int) $request->input("count");
            $countInPAge = isset($count) ? $count : 10;
            $users = User::users()->with('roles')->where([
                ["phone", "like", "%$searchChar%"],
            ])->orWhere([
                ["email", "like", "%$searchChar%"],
            ])->paginate($countInPAge);
            return response()->json([
                'data' => $users,
                'message' => "کاربران با موفقیت دریافت شدند"
            ], 200);
        }
        $users = User::users()->with('roles')->where([
            ["phone", "like", "%$searchChar%"],
        ])->orWhere([
            ["email", "like", "%$searchChar%"],
        ])->get();
        $userCount = sizeof($users);
        return response()->json([
            'data' => $users,
            'message' => $userCount > 0 ? "تعداد $userCount کاربر دریافت شد" : "فعلا کاربری ایجاد نشده است"
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return Response
     */
    /**
     * @OA\Post(
     * path="/api/admin/users",
     * summary="Add users",
     * description="store one users",
     * operationId="addUser",
     * tags={"Users"},
     * security={ {"bearer_token": {} }},
     *  @OA\RequestBody(
     *    required=true,
     *    description="add one category",
     *  @OA\MediaType(
     *    mediaType="application/json",
     *    @OA\Schema(
     *       required={"user_name", "phone", "password"},
     *      @OA\Property(property="user_name", type="string",  example="test username"),
     *      @OA\Property(property="first_name", type="string",  example="test name"),
     *      @OA\Property(property="last_name", type="string",  example="test family"),
     *      @OA\Property(property="phone", type="number", example="09110000011"),
     *      @OA\Property(property="national_code", type="number", example="1111111111"),
     *      @OA\Property(property="email", type="string", example="testemail@gmail.com"),
     *      @OA\Property(property="password", type="string", example="12345678"),
     *      @OA\Property(property="birth_date", type="string", example="2000-10-10"),
     *      @OA\Property(property="gender", type="number", example="1"),
     *      @OA\Property(property="roles_id", type="object", example="[2,3]"),
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
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all() , [
            'user_name' => 'required|regex:/^[a-zA-z0-9\-0-9-@#$_.\n\s]+$/|unique:users,user_name',
            'first_name' => 'nullable|regex:/^[a-zA-z0-9\-0-9ء-ئ., ؟!:.،\n آابپتثجچحخدذرزژسشصضطظعغفقکگل_-منوهیئ\s]+$/',
            'last_name' => 'nullable|regex:/^[a-zA-z0-9\-0-9ء-ئ., ؟!:.،\n آابپتثجچحخدذرزژسشصضطظعغفقکگل_-منوهیئ\s]+$/',
            'phone' => 'required|numeric|digits:11|unique:users,phone',
            'national_code' => 'nullable|numeric|digits:10|unique:users,national_code',
            'email' => 'nullable|email|unique:users,email',
            'password' => 'required|regex:/^[a-zA-z0-9\-0-9-@#$_.\n\s]+$/|between:8,20',
            'birth_date' => 'nullable|date',
            'gender' => 'nullable|numeric|digits:1',
            'roles_id' => 'required|array|min:1',
            'roles_id.*' => 'exists:roles,id|not_in:1',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 202);
        }

        $user = new User;
        $user->user_name = $request['user_name'];
        $user->first_name = $request['first_name'];
        $user->last_name = $request['last_name'];
        $user->phone = $request['phone'];
        $user->national_code = $request['national_code'];
        $user->email = $request['email'];
        $user->password = bcrypt($request['password']);
        $user->birth_date = $request['birth_date'];
        $user->gender = $request['gender'];

        $user->save();

        $user->roles()->attach($request['roles_id']);

        return response()->json([
            'data'=> User::users()->with('roles')->find($user->id),
            'message' => 'کاربر با موفقیت ایجاد شد'
        ] , 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    /**
     * @OA\Get(
     *      path="/api/admin/users/{id}",
     *      summary="Get one users",
     *      description="Get one user with id",
     *      operationId="oneUser",
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
        $user = User::users()->with('roles.permissions')->find($id);
        return response()->json([
            'data' => $user,
            'message' => $user ?"کاربر با موفقیت دریافت شد" : "کاربری یافت نشد"
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
     * path="/api/admin/users/{id}",
     * summary="Edit user",
     * description="Edit one user",
     * operationId="editUser",
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
     *              @OA\Property(property="user_name", type="string",  example="test username edited"),
     *              @OA\Property(property="first_name", type="string",  example="test name edited"),
     *              @OA\Property(property="last_name", type="string",  example="test family edited"),
     *              @OA\Property(property="phone", type="number", example="09110000012"),
     *              @OA\Property(property="national_code", type="number", example="2222222222"),
     *              @OA\Property(property="email", type="string", example="testemail_edited@gmail.com"),
     *              @OA\Property(property="password", type="string", example=""),
     *              @OA\Property(property="birth_date", type="string", example="2000-11-10"),
     *              @OA\Property(property="gender", type="number", example="1"),
     *              @OA\Property(property="roles_id", type="object", example="[4,5]"),
     *          ),
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
    public function update(Request $request,int $id)
    {
        $validator = Validator::make($request->all() , [
            'user_name' => "required|regex:/^[a-zA-z0-9\-0-9-@#\$_.\n\s]+$/|unique:users,user_name,$id,id",
            'first_name' => 'nullable|regex:/^[a-zA-z0-9\-0-9ء-ئ., ؟!:.،\n آابپتثجچحخدذرزژسشصضطظعغفقکگل_-منوهیئ\s]+$/',
            'last_name' => 'nullable|regex:/^[a-zA-z0-9\-0-9ء-ئ., ؟!:.،\n آابپتثجچحخدذرزژسشصضطظعغفقکگل_-منوهیئ\s]+$/',
            'phone' => "required|numeric|digits:11|unique:users,phone,$id,id",
            'national_code' => "nullable|numeric|digits:10|unique:users,national_code,$id,id",
            'email' => "nullable|email|unique:users,email,$id,id",
            'password' => 'nullable|regex:/^[a-zA-z0-9\-0-9-@#$_.\n\s]+$/|between:8,20',
            'birth_date' => 'nullable|date',
            'gender' => 'nullable|numeric|digits:1',
            'roles_id' => 'required|array|min:1',
            'roles_id.*' => 'exists:roles,id|not_in:1',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 202);
        }

        $user = User::users()->find($id);
        $user->user_name = $request['user_name'];
        $user->first_name = $request['first_name'];
        $user->last_name = $request['last_name'];
        $user->phone = $request['phone'];
        $user->national_code = $request['national_code'];
        $user->email = $request['email'];
        if ($request['password']) {
            $user->password = bcrypt($request['password']);
        }
        $user->birth_date = $request['birth_date'];
        $user->gender = $request['gender'];

        $user->save();

        $user->roles()->sync($request['roles_id'], true);

        return response()->json([
            'data'=> User::users()->with('roles')->find($id),
            'message' => 'کاربر با موفقیت ویرایش شد'
        ] , 200);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    /**
     * @OA\Delete(
     * path="/api/admin/users/{id}",
     * summary="Delete user",
     * description="Delete one user",
     * operationId="deleteUser",
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
        User::users()->where('id', $id)->delete();
        return response()->json([
            'message' => 'کاربر با موفقیت حذف شد'
        ] , 200);
    }
}
