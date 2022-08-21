<?php

namespace App\Http\Controllers;

use App\Models\Discount;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
        $discounts = Discount::all();
        $discountsCount = sizeof($discounts);
        return response()->json([
            'data' => $discounts,
            'message' => $discountsCount > 0 ? "تعداد $discountsCount کد تخفیف دریافت شد" : "هنوز کد تخفیفی ایجاد نشده است"
        ] , 200);
    }
}
