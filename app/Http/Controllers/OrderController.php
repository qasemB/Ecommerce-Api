<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Discount;
use App\Models\Item;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    /**
     * @OA\Get(
     *  path="/api/admin/orders",
     *  summary="Get all orders",
     *  description="Get all orders - Note: searchChar is part of user phone number",
     *  operationId="allOrders",
     *  tags={"Orders"},
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
     *      @OA\Schema(type="string", description="search on phone number", example="0911")
     *  ),
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
    public function index(Request $request)
    {
        $page = (int) $request->input("page");
        $searchChar = $request->input("searchChar") ?  $request->input("searchChar") : "";
        $validator = Validator::make(['searchChar'=>$searchChar] , [
            'searchChar' => 'nullable|numeric',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 202);
        }
        if ($page) {
            $count = (int) $request->input("count");
            $countInPAge = isset($count) ? $count : 10;
            $order = Order::with('user')->whereHas('user', function($q) use($searchChar){
                $q->where("phone", "like", "%$searchChar%");
            })->paginate($countInPAge);
            return response()->json([
                'data' => $order,
                'message' => "اطلاعات با موفقیت دریافت شدند"
            ], 200);
        }
        $order = Order::with('user')->whereHas('user', function($q) use($searchChar){
            $q->where("phone", "like", "%$searchChar%");
        })->get();
        $orderCount = sizeof($order);
        return response()->json([
            'data' => $order,
            'message' => $orderCount > 0 ? "تعداد $orderCount سفارش دریافت شد" : "موردی یافت نشد"
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
     * path="/api/admin/orders",
     * summary="Add order",
     * description="store one order",
     * operationId="addOrder",
     * tags={"Orders"},
     * security={ {"bearer_token": {} }},
     *  @OA\RequestBody(
     *    required=true,
     *    description="send cart id and other info",
     *  @OA\MediaType(
     *    mediaType="application/json",
     *    @OA\Schema(
     *      required={"cart_id","delivery_id","address","phone","pay_card_number"},
     *      @OA\Property(property="cart_id", type="number",  example="10"),
     *      @OA\Property(property="discount_id", type="number",  example="1"),
     *      @OA\Property(property="delivery_id", type="number",  example="1"),
     *      @OA\Property(property="address", type="string",  example="test city ... 21th street"),
     *      @OA\Property(property="phone", type="string",  example="09120000000"),
     *      @OA\Property(property="email", type="string",  example="test@info.co"),
     *      @OA\Property(property="pay_card_number", type="string",  example="1111000011110000"),
     *      @OA\Property(property="pay_bank", type="string",  example="test bank"),
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
            'cart_id' => 'required|exists:carts,id|unique:orders,cart_id',
            'discount_id' => 'nullable|exists:discounts,id',
            'delivery_id' => 'required|exists:deliveries,id',
            'address' => 'required|regex:/^[a-zA-z0-9\-0-9ء-ئ., ؟!:.،\n آابپتثجچحخدذرزژسشصضطظعغفقکگلمنوهیئ\s]+$/',
            'phone' => 'required|numeric',
            'email' => 'nullable|email',
            'pay_card_number' => 'required|numeric|digits:16',
            'pay_bank' => 'nullable|regex:/^[a-zA-z0-9\-0-9ء-ئ., ؟!:.،\n آابپتثجچحخدذرزژسشصضطظعغفقکگلمنوهیئ\s]+$/',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 202);
        }

        $cart = Cart::with("user", "items.product")->find($request['cart_id']);
        $user = $cart->user;
        $items = $cart->items;
        $amount=0;
        $itemIds = [];
        foreach ($items as $key => $item) {
            $amount += $item->product->price * $item->count;
            array_push($itemIds, $item->id);
        }

        $discount = $request['discount_id'] ? Discount::find($request['discount_id']) : null;
        $discountPrice = 0;
        if ($discount) {
            $discountPrice = ($discount->percent / 100) * $amount;
        }

        $order = new Order;
        $order->user_id = $cart->user_id;
        $order->cart_id = $cart->id;
        $order->discount_id = $request['discount_id'];
        $order->delivery_id = $request['delivery_id'];
        $order->user_fullname = $user->first_name." ".$user->last_name;
        $order->amount = $amount;
        $order->discount_price = $discountPrice;
        $order->address = $request['address'];
        $order->phone = $request['phone'];
        $order->email = $request['email'];
        $order->pay_amount = $amount - $discountPrice;
        $order->pay_card_number = $request['pay_card_number'];
        $order->pay_bank = $request['pay_bank'];
        $order->save();

        $cart->is_ordered = 1;
        $cart->save();

        foreach ($items as $key => $item) {
            Item::where('id', $item->id)->update(['unit_price' => $item->product->price]);
        }

        return response()->json([
            'data' => $order,
            'message' => 'سفارش با موفقیت ایجاد شد'
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
     *      path="/api/admin/orders/{id}",
     *      summary="Get one order",
     *      description="Get one order with id",
     *      operationId="oneOrder",
     *      tags={"Orders"},
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
        $order = Order::with("user", "cart.items.product", "cart.items.color", "cart.items.guarantee")->find($id);
        return response()->json([
            'data' => $order,
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
     * path="/api/admin/orders/{id}",
     * summary="Delete orders",
     * description="Delete one order",
     * operationId="deleteOrder",
     * tags={"Orders"},
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
        Order::destroy($id);
        return response()->json([
            'message' => 'سفارش با موفقیت حذف شد'
        ] , 200);
    }
}
