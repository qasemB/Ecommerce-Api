<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    /**
     * @OA\Get(
     *  path="/api/admin/products",
     *  summary="Get products",
     *  description="get all products or with pagination",
     *  operationId="getProducts",
     *  tags={"Products"},
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
        $page = $request->input("page");
        if (isset($page)) {
            $count = $request->input("count");
            $countInPAge = isset($count) ? $count : 10;
            $products = Product::paginate($countInPAge);
            $productsCount = sizeof($products);
            return response()->json($products, 200);
        }
        $products = Product::all();
        $productsCount = sizeof($products);
        return response()->json([
            'data' => $products,
            'message' => $productsCount > 0 ? "تعداد $productsCount محصول دریافت شد" : "فعلا محصولی ایجاد نشده است"
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
     * path="/api/admin/products",
     * summary="Add product",
     * description="store one product",
     * operationId="addProduct",
     * tags={"Products"},
     * security={ {"bearer_token": {} }},
     *  @OA\RequestBody(
     *    required=true,
     *    description="add one category",
     *  @OA\MediaType(
     *    mediaType="multipart/form-data",
     *    @OA\Schema(
     *       required={"title", "price"},
     *      @OA\Property(property="title", type="string",  example="product test"),
     *      @OA\Property(property="price", type="number",  example="1500"),
     *      @OA\Property(property="weight", type="string",  example="2"),
     *      @OA\Property(property="brand_id", type="number", example="1"),
     *      @OA\Property(property="descriptions", type="string", example="Description test for this product..."),
     *      @OA\Property(property="short_descriptions", type="string", example="Description test for this product..."),
     *      @OA\Property(property="cart_descriptions", type="string", example="Description test for this product when user selcted it in cart..."),
     *      @OA\Property(property="image", description="file to upload",type="file"),
     *      @OA\Property(property="alt_image", type="string", example="Some keyword"),
     *      @OA\Property(property="keywords", type="string", example="keyword1-keyword2-keyword3"),
     *      @OA\Property(property="stock", type="number", example="keyword1-keyword2-keyword3"),
     *      @OA\Property(property="discount", type="number", example="10"),
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
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
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
     * @return Response
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
    public function destroy($id)
    {
        //
    }
}
