<?php

namespace App\Http\Controllers;

use App\Models\Color;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{

    /**
     * @OA\Post(
     * path="/api/auth/register",
     * summary="Sign up",
     * description="register by phone and password",
     * operationId="authRegister",
     * tags={"Auth"},
     * @OA\RequestBody(
     *    required=true,
     *    description="Pass user credentials",
     *    @OA\JsonContent(
     *       required={"phone","password","c_password"},
     *       @OA\Property(property="phone", type="string", format="email", example="09110001100"),
     *       @OA\Property(property="password", type="string", format="password", example="123456"),
     *       @OA\Property(property="c_password", type="string", format="password", example="123456"),
     *    ),
     * ),
     * @OA\Response(
     *    response=201,
     *    description="success",
     *    @OA\JsonContent(
     *       @OA\Property(property="token", type="string", example="bsgdjkdwd54sfdsf6d54f6..."),
     *       @OA\Property(property="token_type", type="string", example="Bearer"),
     *       @OA\Property(property="expires_at", type="string", example="2027-03-30 14:42:04"),
     *        )
     *     )
     * )
     */
    public function register(Request $request){
        $validator = Validator::make($request->all() , [
            'phone' => 'required|string|unique:users,phone|numeric|digits:11',
            'password' => 'required|max:12|min:6',
            'c_password' => 'required|same:password',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors() , 202);
        }

        $user = new User();
        $user->phone = $request->phone;
        $user->password = bcrypt($request->password);
        $user->save();

        return response()->json([
            "token" => $user->createToken('personalToken')->accessToken,
            "message" => "$user->phone با موفقیت ثبت نام شد..."
        ] , 200);
    }



    /**
     * @OA\Post(
     * path="/api/auth/login",
     * summary="Sign in",
     * description="login by phone and password",
     * operationId="authLogin",
     * tags={"Auth"},
     * @OA\RequestBody(
     *    required=true,
     *    description="Pass user credentials",
     *    @OA\JsonContent(
     *       required={"phone","password"},
     *       @OA\Property(property="phone", type="string", example="09110001100"),
     *       @OA\Property(property="password", type="string", example="Pass111$"),
     *       @OA\Property(property="remember", type="number", example="1"),
     *    ),
     * ),
     * @OA\Response(
     *    response=200,
     *    description="success",
     *    @OA\JsonContent(
     *       @OA\Property(property="token", type="string", example="bsgdjkdwd54sfdsf6d54f6..."),
     *       @OA\Property(property="token_type", type="string", example="Bearer"),
     *       @OA\Property(property="expires_at", type="string", example="2027-03-30 14:42:04"),
     *        )
     *     )
     * )
     */
    public function login(Request $request){
        $validator = Validator::make($request->all() , [
            'phone' => 'required|string|numeric|digits:11',
            'password' => 'required',
            'remember' => 'nullable | numeric',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors() , 202);
        }

//        dd($request->all());
        if (Auth::attempt(['phone' => $request->phone, 'password' => $request->password])) {
            $user = Auth::user();
            $tokenRes = $user->createToken('personalToken');
            $token = $tokenRes->token;

            if ($request->remember) {
                $token->expires_at = Carbon::now()->addYear(5);
            }else{
                $token->expires_at = Carbon::now()->addHour(1);
            }

            $token->save();

            return response()->json([
                'token'=>$tokenRes->accessToken,
                'token_type'=>"Bearer",
                'expires_at'=> Carbon::parse($tokenRes->token->expires_at)->toDateTimeString()
            ],200);
        }else{
            return response()->json([
                "message" => "مشخصات وارد شده صحیح نمی باشند"
            ] , 203);
        }
    }



    /**
     * @OA\Get(
     *  path="/api/auth/logout",
     *  summary="sign out user",
     *  operationId="logout",
     *  tags={"Auth"},
     *  security={ {"bearer_token": {} }},
     *  @OA\Response(
     *    response=200,
     *    description="success"
     *  )
     * )
     */
    public function logout(Request $request){
        $request->user()->token()->revoke();
        return response()->json([
            'message' => $request->user()->name."شما با موفقیت خارج شد"
        ] , 200);
    }



    /**
     * @OA\Get(
     *  path="/api/auth/user",
     *  summary="logined user data",
     *  description="get user data after authentication",
     *  operationId="userData",
     *  tags={"Auth"},
     *  security={ {"bearer_token": {} }},
     *  @OA\Response(
     *    response=200,
     *    description="success",
     *    @OA\JsonContent(
     *       @OA\Property(property="token", type="string", example="bsgdjkdwd54sfdsf6d54f6...")
     *     )
     *  )
     * )
     */
    public function getUser(Request $request){
        try {
            return $request->user()->load(['roles.permissions']);
        } catch (\Throwable $th) {
            return response()->json([
                "message" => "دسترسی به کاربر امکان پذیر نیست"
            ] , 203);
        }
        // return response()->json([
        //     'id'=>$user->id,
        //     'name'=>$user->name,
        //     'email'=> $user->email,
        // ],200);
    }
}
