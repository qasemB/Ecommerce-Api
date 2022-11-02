<?php

namespace App\Http\Controllers;

use App\Models\Discount;
use Illuminate\Http\Response;
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
     *    description="Add one discount",
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


    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param  int  $id
     * @return Response
     */
    /**
     * @OA\Put(
     * path="/api/admin/discounts/{id}",
     * summary="Edit discount",
     * description="Edit one discount",
     * operationId="editDiscount",
     * tags={"Discounts"},
     * security={ {"bearer_token": {} }},
     *  @OA\Parameter(
     *      in="path",
     *      name="id",
     *      required=true,
     *      @OA\Schema(type="string")
     *  ),
     *  @OA\RequestBody(
     *      required=true,
     *      description="edit delivery",
     *      @OA\MediaType(
     *          mediaType="application/json",
     *          @OA\Schema(
     *              @OA\Property(property="title", type="string"),
     *              @OA\Property(property="code", type="string"),
     *              @OA\Property(property="percent", type="number"),
     *              @OA\Property(property="expire_at", type="string"),
     *              @OA\Property(property="for_all", type="boolean"),
     *              @OA\Property(property="product_ids", type="string"),
     *          ),
     *        example={
     *          "title" : "edited discount title",
     *          "code" : "takhfif edited",
     *          "percent" : "11",
     *          "expire_at" : "2022-09-08",
     *          "for_all" : "true",
     *          "product_ids" : "30-25",
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
    public function update(Request $request, int $id): JsonResponse
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


        $discount = Discount::find($id);
        $discount->title = $request['title'];
        $discount->code = $request['code'];
        $discount->percent = $request['percent'];
        $discount->expire_at = $request['expire_at'];
        $discount->for_all = $request['for_all'];

        if (!$request['for_all']) {
            $discount->products()->sync(explode("-", $request['product_ids']), true);
        }

        $discount->save();

        $discount = Discount::with('products')->find($id);


        return response()->json([
            'data'=> $discount,
            'message' => 'کد با موفقیت ویرایش شد'
        ] , 200);
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    /**
     * @OA\Delete(
     * path="/api/admin/discounts/{id}",
     * summary="Delete discounts",
     * description="Delete one discount",
     * operationId="deleteDiscount",
     * tags={"Discounts"},
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
    public function destroy(int $id): JsonResponse
    {
        Discount::destroy($id);
        return response()->json([
            'message' => 'کد تخفیف با موفقیت حذف شد'
        ] , 200);
    }


}
