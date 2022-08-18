<?php

namespace App\Http\Controllers;

use App\Models\Gallery;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
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
    public function index(Request $request): JsonResponse
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
            $products = Product::with('categories', 'colors', 'guarantees', 'attributes', 'gallery')->where("title", "like", "%$searchChar%")->paginate($countInPAge);
            return response()->json($products, 200);
        }
        $products = Product::with('categories', 'colors', 'guarantees', 'attributes', 'gallery')->where("title", "like", "%$searchChar%")->get();
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
     *       required={"category_ids", "title", "price"},
     *      @OA\Property(property="category_ids", type="string", example="1-2-3"),
     *      @OA\Property(property="title", type="string",  example="product test"),
     *      @OA\Property(property="price", type="number",  example="1500"),
     *      @OA\Property(property="weight", type="string",  example="2"),
     *      @OA\Property(property="brand_id", type="number", example="1"),
     *      @OA\Property(property="color_ids", type="string", example="1-2-3"),
     *      @OA\Property(property="guarantee_ids", type="string", example="1-2-3"),
     *      @OA\Property(property="descriptions", type="string", example="Description test for this product..."),
     *      @OA\Property(property="short_descriptions", type="string", example="Description test for this product..."),
     *      @OA\Property(property="cart_descriptions", type="string", example="Description test for this product when user selcted it in cart..."),
     *      @OA\Property(property="image", description="file to upload",type="file"),
     *      @OA\Property(property="alt_image", type="string", example="Some keyword"),
     *      @OA\Property(property="keywords", type="string", example="keyword1-keyword2-keyword3"),
     *      @OA\Property(property="stock", type="number", example="10"),
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
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all() , [
            'category_ids' => 'required|regex:/^[0-9\s-]+$/',
            'title' => 'required|regex:/^[a-zA-z0-9\-0-9ء-ئ., ؟!:.،\n آابپتثجچحخدذرزژسشصضطظعغفقکگلمنوهیئ\s]+$/',
            'price' => 'required|numeric',
            'weight' => 'nullable|regex:/^[0-9.\s]+$/',
            'brand_id' => 'nullable|numeric',
            'color_ids' => 'nullable|regex:/^[0-9\s-]+$/',
            'guarantee_ids' => 'nullable|regex:/^[0-9-\s]+$/',
            'descriptions' => 'nullable',
            'short_descriptions' => 'nullable|regex:/^[a-zA-z0-9\-0-9ء-ئ., ؟!:.،\n آابپتثجچحخدذرزژسشصضطظعغفقکگلمنوهیئ\s]+$/',
            'cart_descriptions' => 'nullable|regex:/^[a-zA-z0-9\-0-9ء-ئ., ؟!:.،\n آابپتثجچحخدذرزژسشصضطظعغفقکگلمنوهیئ\s]+$/',
            'image' => 'nullable|image|max:500' ,
            'alt_image' => 'nullable|regex:/^[a-zA-z0-9\-0-9ء-ئ., ؟!:.،\n آابپتثجچحخدذرزژسشصضطظعغفقکگلمنوهیئ\s]+$/',
            'keywords' => 'nullable|regex:/^[a-zA-z0-9\-0-9ء-ئ., ؟!:.،\n آابپتثجچحخدذرزژسشص-ضطظعغفقکگلمنوهیئ\s]+$/',
            'stock' => 'nullable|numeric',
            'discount' => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 202);
        }

        $product = new Product;
        $product->title = $request['title'];
        $product->price = $request['price'];
        $product->weight = $request['weight'];
        $product->brand_id = $request['brand_id'];
        $product->descriptions = $request['descriptions'];
        $product->short_descriptions = $request['short_descriptions'];
        $product->cart_descriptions = $request['cart_descriptions'];
        $product->alt_image = $request['alt_image'];
        $product->keywords = $request['keywords'];
        $product->stock = $request['stock'];
        $product->discount = $request['discount'];

        $product->save();

        $product->categories()->attach(explode("-", $request['category_ids']));

        if ($request->file('image')) {
            $imgpath = Storage::disk('public')->put("images/products/$product->id", $request->file('image'));
            $product->image = $imgpath;
            $gallery = new Gallery();
            $gallery->product_id = $product->id;
            $gallery->image = $imgpath;
            $gallery->is_main = 1;
            $gallery->save();
        }
        if ($request['color_ids']) {
            $product->colors()->attach(explode("-", $request['color_ids']));
        }
        if ($request['guarantee_ids']) {
            $product->guarantees()->attach(explode("-", $request['guarantee_ids']));
        }

        $product->save();

        return response()->json([
            'data'=> Product::with('categories', 'colors', 'guarantees')->find($product->id),
            'message' => 'محصول با موفقیت ایجاد شد'
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
     *      path="/api/admin/products/{id}",
     *      summary="Get one product",
     *      description="Get one product with id",
     *      operationId="oneProduct",
     *      tags={"Products"},
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
    public function show($id): JsonResponse
    {
        $product = Product::with('categories', 'colors', 'guarantees')->find((int)$id);
        if ($product) {
            $productTitle = $product->title;
            return response()->json([
                "data" => $product,
                'message' => "این محصول دریافت شد: $productTitle"
            ] , 200);
        }else{
            return response()->json([
                "message" => "هیچ موردی یافت نشد"
            ] , 404);
        }
    }

    /**
     * @OA\Get(
     *      path="/api/admin/products/title_is_exist/{title}",
     *      summary="Is exist product title",
     *      description="Get status of product existation",
     *      operationId="productTitleExist",
     *      tags={"Products"},
     *      security={ {"bearer_token": {} }},
     *  @OA\Parameter(
     *      in="path",
     *      name="title",
     *      required=true,
     *      @OA\Schema(type="string")
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
    public function titleIsExist($title): JsonResponse
    {
        $validator = Validator::make(['title'=>$title] , [
            'title' => 'required|regex:/^[a-zA-z0-9\-0-9ء-ئ., ؟!:.،\n آابپتثجچحخدذرزژسشصضطظعغفقکگلمنوهیئ\s]+$/',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 202);
        }

        $isExist = Product::where('title', $title)->exists();
        return response()->json([
            'isExist' => $isExist,
            'message' => $isExist ? "این نام قبلا انتخاب شده است" : "این نام قبلا نشده است"
        ] , 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return Response
     */
    /**
     * @OA\Put(
     * path="/api/admin/products/{id}",
     * summary="Edit product",
     * description="Edit one product",
     * operationId="editProduct",
     * tags={"Products"},
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
     *              @OA\Property(property="price", type="number"),
     *              @OA\Property(property="weight", type="string"),
     *              @OA\Property(property="brand_id", type="number"),
     *              @OA\Property(property="descriptions", type="string"),
     *              @OA\Property(property="short_descriptions", type="string"),
     *              @OA\Property(property="cart_descriptions", type="string"),
     *              @OA\Property(property="alt_image", type="string"),
     *              @OA\Property(property="keywords", type="string"),
     *              @OA\Property(property="stock", type="number"),
     *              @OA\Property(property="discount", type="number"),
     *          ),
     *        example={
     *          "title" : "example title edited",
     *          "price" : "2000",
     *          "weight" : "2",
     *          "brand_id" : "15",
     *          "descriptions" : "edited description...",
     *          "short_descriptions" : "edited short descriptions...",
     *          "cart_descriptions" : "edited cart descriptions...",
     *          "alt_image" : "edited alt image",
     *          "keywords" : "edited keywords1-edited keywords2",
     *          "stock" : "5",
     *          "discount" : "50",
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
            'category_ids' => 'required|regex:/^[0-9\s-]+$/',
            'color_ids' => 'nullable|regex:/^[0-9\s-]+$/',
            'guarantee_ids' => 'nullable|regex:/^[0-9-\s]+$/',
            'title' => 'required|regex:/^[a-zA-z0-9\-0-9ء-ئ., ؟!:.،\n آابپتثجچحخدذرزژسشصضطظعغفقکگلمنوهیئ\s]+$/',
            'price' => 'required|numeric',
            'weight' => 'nullable|regex:/^[0-9.\s]+$/',
            'brand_id' => 'nullable|numeric',
            'descriptions' => 'nullable',
            'short_descriptions' => 'nullable|regex:/^[a-zA-z0-9\-0-9ء-ئ., ؟!:.،\n آابپتثجچحخدذرزژسشصضطظعغفقکگلمنوهیئ\s]+$/',
            'cart_descriptions' => 'nullable|regex:/^[a-zA-z0-9\-0-9ء-ئ., ؟!:.،\n آابپتثجچحخدذرزژسشصضطظعغفقکگلمنوهیئ\s]+$/',
            'alt_image' => 'nullable|regex:/^[a-zA-z0-9\-0-9ء-ئ., ؟!:.،\n آابپتثجچحخدذرزژسشصضطظعغفقکگلمنوهیئ\s]+$/',
            'keywords' => 'nullable|regex:/^[a-zA-z0-9\-0-9ء-ئ., ؟!:.،\n آابپتثجچحخدذرزژسشص-ضطظعغفقکگلمنوهیئ\s]+$/',
            'stock' => 'nullable|numeric',
            'discount' => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 202);
        }


        $product = Product::with('categories', 'colors', 'guarantees')->find($id);
        $product->title = $request['title'];
        $product->price = $request['price'];
        $product->weight = $request['weight'];
        $product->brand_id = $request['brand_id'];
        $product->descriptions = $request['descriptions'];
        $product->short_descriptions = $request['short_descriptions'];
        $product->cart_descriptions = $request['cart_descriptions'];
        $product->alt_image = $request['alt_image'];
        $product->keywords = $request['keywords'];
        $product->stock = $request['stock'];
        $product->discount = $request['discount'];


        $product->categories()->sync(explode("-", $request['category_ids']), true);

        if ($request['color_ids']) {
            $product->colors()->sync(explode("-", $request['color_ids']), true);
        }
        if ($request['guarantee_ids']) {
            $product->guarantees()->sync(explode("-", $request['guarantee_ids']), true);
        }


        $product->save();

        return response()->json([
            'data'=> $product,
            'message' => 'محصول با موفقیت ویرایش شد'
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
     * path="/api/admin/products/{id}",
     * summary="Delete product",
     * description="Delete one product",
     * operationId="deleteProduct",
     * tags={"Products"},
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
        $product = Product::find($id);
        $imagePath = $product->image;
        if (File::isDirectory("images/products/$id"))
            File::deleteDirectory("images/products/$id",0755);
        Product::destroy($id);
        return response()->json([
            'message' => 'محصول با موفقیت حذف شد'
        ] , 200);
    }

    /**
     * @OA\Post(
     * path="/api/admin/products/{id}/add_attr",
     * summary="Add product Attributes",
     * description="Add multiple attribute for one product",
     * operationId="productAttr",
     * tags={"Products"},
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
     *              @OA\Property(property="1", type="string"),
     *              @OA\Property(property="2", type="string"),
     *              @OA\Property(property="3", type="string"),
     *          ),
     *        example={
     *          "1" : {"value": "test1"},
     *          "2" : {"value": "test2"},
     *          "3" : {"value": "test3"},
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
    public function addAttr(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all() , [
            '*.value' => 'required|regex:/^[a-zA-z0-9\-0-9ء-ئ., ؟!:.،\n آابپتثجچحخدذرزژسشصضطظعغفقکگلمنوهیئ\s]+$/',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 202);
        }

        $data = $request->all();
        $product = Product::find($id);
        $product->attributes()->sync($data, true);

        $attrs = Product::with('attributes')->where('id', $id)->first()->attributes;

        return response()->json([
            "data" => $attrs,
            'message' => "عملیات با موفقیت انجام شد"
        ] , 200);
    }

    /**
     * @OA\Get(
     * path="/api/admin/products/{id}/get_attr",
     * summary="Get product Attributes",
     * description="Get all attributes from one product. You need to pass Product id",
     * operationId="getProductAttr",
     * tags={"Products"},
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
     *       @OA\Property(property="message", type="string", example="عملیت با موفقیت انجام شد"),
     *        )
     *     )
     * )
     */
    public function getProductAttrs(int $id): JsonResponse
    {
        $priductAttrs = Product::with('attributes')->where('id', $id)->first()->attributes;

        return response()->json([
            "data" => $priductAttrs,
            'message' => sizeof($priductAttrs) > 0 ? "عملیات با موفقیت انجام شد" : "ویژگی های این محصول مقداردهی نشده است"
        ] , 200);
    }

    /**
     * @OA\Post(
     * path="/api/admin/products/{id}/add_image",
     * summary="Add image in gallery",
     * description="Add one image in gallery list with product id",
     * operationId="AddGalleryImage",
     * tags={"Products"},
     * security={ {"bearer_token": {} }},
     *  @OA\Parameter(
     *      in="path",
     *      name="id",
     *      required=true,
     *      @OA\Schema(type="string")
     *  ),
     * @OA\RequestBody(
     *    required=true,
     *    description="Add one image",
     *  @OA\MediaType(
     *    mediaType="multipart/form-data",
     *    @OA\Schema(
     *       required={"image"},
     *      @OA\Property(property="image", description="Image to upload",type="file"),
     *    )
     *   ),
     * ),
     * @OA\Response(
     *    response=201,
     *    description="success",
     *    @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="عملیت با موفقیت انجام شد"),
     *        )
     *     )
     * )
     */
    public function addImage(Request $request, int $id){
        $validator = Validator::make($request->all() , [
            'image' => 'required|image|max:500' ,
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 202);
        }

        $imgpath = Storage::disk('public')->put("images/products/$id", $request->file('image'));
        $gallery = new Gallery();
        $gallery->product_id = $id;
        $gallery->image = $imgpath;
        $gallery->save();

        return response()->json([
            'data' => ['id' => $gallery->id, 'image' => $imgpath],
            'message' => "تصویر با موفقیت ذخیره شد"
        ] , 201);
    }

    /**
     * @OA\Delete(
     * path="/api/admin/products/gallery/{imageId}",
     * summary="Delete product image",
     * description="Delete one product image",
     * operationId="deleteProductImage",
     * tags={"Products"},
     * security={ {"bearer_token": {} }},
     *  @OA\Parameter(
     *      in="path",
     *      name="imageId",
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
    public function deleteImage(int $id)
    {
        $image = Gallery::find($id);
        $imagePath = $image->image;
        if (isset($imagePath) && File::exists($imagePath)) File::delete($imagePath);
        Gallery::destroy($id);
        return response()->json([
            'message' => "تصویر با موفقیت حذف شد"
        ] , 200);
    }

}
