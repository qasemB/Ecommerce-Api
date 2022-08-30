<?php

namespace App\Http\Controllers;

use App\Models\Discount;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DiscountController extends Controller
{
    /**
     * @OA\Get(
     *  path="/api/admin/discounts",
     *  summary="Get all discounts",
     *  description="Get all discounts",
     *  operationId="allDiscounts",
     *  tags={"Discounts"},
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
    public function index(): JsonResponse
    {
        $discounts = Discount::with('products')->get();
        $discountsCount = sizeof($discounts);
        return response()->json([
            'data' => $discounts,
            'message' => $discountsCount > 0 ? "تعداد $discountsCount کد تخفیف دریافت شد" : "هنوز کد تخفیفی ایجاد نشده است"
        ] , 200);
    }


    /**
     * @OA\Post(
     * path="/api/admin/discounts",
     * summary="Add discount code",
     * description="Store one discount code",
     * operationId="addDiscount",
     * tags={"Discounts"},
     * security={ {"bearer_token": {} }},
     *  @OA\RequestBody(
     *    required=true,
     *    description="Add one color",
     *  @OA\MediaType(
     *    mediaType="application/json",
     *    @OA\Schema(
     *       required={"title", "code", "percent", "expire_at", "for_all"},
     *      @OA\Property(property="title", type="string",  example="codeTest"),
     *      @OA\Property(property="code", type="string",  example="codetakhfif"),
     *      @OA\Property(property="percent", type="number",  example="10"),
     *      @OA\Property(property="expire_at", type="string",  example="2022-08-01"),
     *      @OA\Property(property="for_all", type="boolean",  example="true"),
     *      @OA\Property(property="product_ids", type="string",  example="1-2-3"),
     *    )
     *   ),
     * ),
     * @OA\Response(
     *    response=201,
     *    description="success",
     *    @OA\JsonContent(
     *       @OA\Property(property="data", type="string", example="{...}"),
     *        )
     *     )
     * )
     */
    public function store(Request $request): JsonResponse
    {

        $validator = Validator::make($request->all() , [
            'title' => 'required|unique:colors,title|regex:/^[a-zA-z0-9\-0-9ء-ئ., ؟!:.،\n آابپتثجچحخدذرزژسشصضطظعغفقکگلمنوهیئ\s]+$/' ,
            'code' => 'required|unique:colors,title|regex:/^[a-zA-z0-9\-0-9ء-ئ., ؟!:.،\n @!%-.$?&\s]+$/' ,
            'percent' => 'required|numeric' ,
            'expire_at' => 'required|date' ,
            'for_all' => 'required|boolean' ,
            'product_ids' => 'nullable|regex:/^[0-9 \-]+$/' ,
        ]);


        if ($validator->fails()) {
            return response()->json($validator->errors(), 202);
        }

        $discount = new Discount;
        $discount->title = $request['title'];
        $discount->code = $request['code'];
        $discount->percent = $request['percent'];
        $discount->expire_at = $request['expire_at'];
        $discount->for_all = $request['for_all'];
        $discount->save();

        if (!$request['for_all']) {
            $discount->products()->attach(explode("-", $request['product_ids']));
        }

        $discount = Discount::with('products')->where('id', $discount->id)->first();

        return response()->json([
            'data' => $discount,
            'message' => 'کد تخفیف با موفقیت ایجاد شد'
        ] , 201);


    }




}
