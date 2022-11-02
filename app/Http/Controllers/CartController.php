<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CartController extends Controller
{
    /**
     * @OA\Get(
     *  path="/api/admin/carts",
     *  summary="Get all carts",
     *  description="Get all carts",
     *  operationId="allCarts",
     *  tags={"Carts"},
     *  security={ {"bearer_token": {} }},
     *  @OA\Response(
     *    response=200,
     *    description="success",
     *    @OA\JsonContent(
     *       @OA\Property(property="data", type="string", example="[{}, {}]")
     *     )
     *  )
     * )
     * @return JsonResponse
     */
    public function index()
    {
        $carts = Cart::with("items")->get();
        $cartsCount = sizeof($carts);
        return response()->json([
            'data' => $carts,
            'message' => $cartsCount > 0 ? "تعداد $cartsCount سبد دریافت شد" : "هنوز سبدی ایجاد نشده است"
        ] , 200);
    }



    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return Response
     */
    /**
     * @OA\Post(
     * path="/api/admin/carts",
     * summary="Add cart",
     * description="store one cart",
     * operationId="addCart",
     * tags={"Carts"},
     * security={ {"bearer_token": {} }},
     *  @OA\RequestBody(
     *    required=true,
     *    description="send user id and send product objects ",
     *  @OA\MediaType(
     *    mediaType="application/json",
     *    @OA\Schema(
     *      type = "object",
     *      @OA\Property(property="user_id", type="string",  example="11"),
     *      @OA\Property(property="products", type="array",
     *          @OA\Items(type="object",
     *              @OA\Property(property="product_id", type="number", example="30"),
     *              @OA\Property(property="color_id", type="number", example="2"),
     *              @OA\Property(property="guarantee_id", type="number", example="1"),
     *              @OA\Property(property="count", type="number", example="5"),
     *          )
     *      ),
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
            'user_id' => 'required|exists:users,id',
            'products.*.product_id' => 'required|numeric|exists:products,id',
            'products.*.color_id' => 'required|numeric|exists:colors,id',
            'products.*.guarantee_id' => 'required|numeric|exists:guarantees,id',
            'products.*.count' => 'required|numeric',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 202);
        }

        $cart = new Cart;
        $cart->user_id = $request['user_id'];
        $cart->save();

        $data = [];
        foreach ($request['products'] as $key => $value) {
            array_push($data, [
                "cart_id"=> $cart->id,
                "product_id"=> $value["product_id"],
                "color_id"=> $value["color_id"],
                "guarantee_id"=> $value["guarantee_id"],
                "count"=> $value["count"],
            ]);
        }

        Item::insert($data);

        $thisCart = Cart::with("items")->find($cart->id);

        return response()->json([
            'data' => $thisCart,
            'message' => 'سبد خرید با موفقیت ایجاد شد'
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
     *      path="/api/admin/carts/{id}",
     *      summary="Get one cart",
     *      description="Get one cart with id",
     *      operationId="oneCart",
     *      tags={"Carts"},
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
     *      @OA\Property(property="data", type="string", example="{...}")
     *   )
     *  )
     * )
     */
    public function show($id)
    {
        $cart = Cart::with("items")->find($id);
        return response()->json([
            'data' => $cart,
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
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    /**
     * @OA\Delete(
     * path="/api/admin/carts/{id}",
     * summary="Delete carts",
     * description="Delete one cart",
     * operationId="deleteCart",
     * tags={"Carts"},
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
        Cart::destroy($id);
        return response()->json([
            'message' => 'سبد با موفقیت حذف شد'
        ] , 200);
    }
}
