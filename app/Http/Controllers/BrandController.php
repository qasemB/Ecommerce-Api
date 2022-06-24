<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class BrandController extends Controller
{
    /**
     * @OA\Get(
     *  path="/api/admin/brands",
     *  summary="Get all brands",
     *  description="Get all brands",
     *  operationId="allBrands",
     *  tags={"Brands"},
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
        $brands = Brand::all();
        $brandsCount = sizeof($brands);
        return response()->json([
            'data' => $brands,
            'message' => $brandsCount > 0 ? "تعداد $brandsCount برند دریافت شد" : "هنوز برندی ایجاد نشده است"
        ] , 200);
    }


    /**
     * @OA\Post(
     * path="/api/admin/brands",
     * summary="Add brand",
     * description="Store one brand",
     * operationId="addBrand",
     * tags={"Brands"},
     * security={ {"bearer_token": {} }},
     *  @OA\RequestBody(
     *    required=true,
     *    description="Add one brand",
     *  @OA\MediaType(
     *    mediaType="multipart/form-data",
     *    @OA\Schema(
     *       required={"original_name"},
     *      @OA\Property(property="original_name", type="string",  example="example name"),
     *      @OA\Property(property="persian_name", type="string",  example="نام تست"),
     *      @OA\Property(property="descriptions", type="string",  example="example description"),
     *      @OA\Property(property="logo", description="file to upload",type="file"),
     *    )
     *   ),
     * ),
     * @OA\Response(
     *    response=201,
     *    description="success",
     *    @OA\JsonContent(
     *       @OA\Property(property="data", type="string", example="[{}, {}]"),
     *    )
     *  )
     * )
     */
    public function store(Request $request): JsonResponse
    {
        try {

            $validator = Validator::make($request->all() , [
                'original_name' => 'required|unique:brands,original_name|regex:/^[a-zA-z0-9\s]+$/' ,
                'persian_name' => 'nullable|regex:/^[a-zA-z0-9\-0-9ء-ئ., ؟!:.،\n آابپتثجچحخدذرزژسشصضطظعغفقکگلمنوهیئ\s]+$/' ,
                'descriptions' => 'nullable|regex:/^[a-zA-z0-9\-0-9ء-ئ., ؟!:.،\n آابپتثجچحخدذرزژسشصضطظعغفقکگلمنوهیئ\s]+$/' ,
                'logo' => 'nullable|image|max:500' ,
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 202);
            }

            $brand = new Brand;
            $brand->original_name = $request['original_name'];
            $brand->persian_name = $request['persian_name'];
            $brand->descriptions = $request['descriptions'];

            if ($request->file('logo')) {
                $imagePath = $request->file('logo')->store('public/brands');
                $imagePath = explode('/',$imagePath);
                $imagePath[0] = 'storage';
                $brand->logo = join('/' , $imagePath);
            }

            $brand->save();

            return response()->json([
                'data' => $brand,
                'message' => 'برند با موفقیت ایجاد شد'
            ] , 201);


        } catch (\Throwable $th) {

            return response()->json([
                'message' => 'خطایی در حین ذخیره سازی رخ داد'
            ] , 500);

        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    /**
     * @OA\Get(
     *      path="/api/admin/brands/{id}",
     *      summary="Get one brand",
     *      description="Get one brand with id",
     *      operationId="oneBrand",
     *      tags={"Brands"},
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
        $brand = Brand::find((int)$id);
        if ($brand) {
            $brandTitle = $brand->original_name;
            return response()->json([
                "data" => $brand,
                'message' => "این برند دریافت شد: $brandTitle"
            ] , 200);
        }else{
            return response()->json([
                "message" => "هیچ موردی یافت نشد"
            ] , 404);
        }
    }


    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param  int  $id
     * @return Response
     */
    /**
     * @OA\Post(
     * path="/api/admin/brands/{id}",
     * summary="Update brand",
     * description="Edit one brand",
     * operationId="editBrand",
     * tags={"Brands"},
     * security={ {"bearer_token": {} }},
     *  @OA\Parameter(
     *      in="path",
     *      name="id",
     *      required=true,
     *      @OA\Schema(type="string")
     *  ),
     *  @OA\RequestBody(
     *    required=true,
     *    description="Edit one brand",
     *  @OA\MediaType(
     *    mediaType="multipart/form-data",
     *    @OA\Schema(
     *       required={"original_name"},
     *      @OA\Property(property="original_name", type="string",  example="example name"),
     *      @OA\Property(property="persian_name", type="string",  example="نام تست"),
     *      @OA\Property(property="descriptions", type="string",  example="example description"),
     *      @OA\Property(property="logo", description="file to upload",type="file"),
     *    )
     *   ),
     * ),
     * @OA\Response(
     *    response=201,
     *    description="success",
     *    @OA\JsonContent(
     *       @OA\Property(property="data", type="string", example="{...}"),
     *    )
     *  )
     * )
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {

            $validator = Validator::make($request->all() , [
                'original_name' => "required|unique:brands,original_name,$id,id|regex:/^[a-zA-z0-9\s]+$/" ,
                'persian_name' => 'nullable|regex:/^[a-zA-z0-9\-0-9ء-ئ., ؟!:.،\n آابپتثجچحخدذرزژسشصضطظعغفقکگلمنوهیئ\s]+$/' ,
                'descriptions' => 'nullable|regex:/^[a-zA-z0-9\-0-9ء-ئ., ؟!:.،\n آابپتثجچحخدذرزژسشصضطظعغفقکگلمنوهیئ\s]+$/' ,
                'logo' => 'nullable|image|max:500' ,
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 202);
            }

            $brand = Brand::find((int)$id);
            $brand->original_name = $request['original_name'];
            $brand->persian_name = $request['persian_name'];
            $brand->descriptions = $request['descriptions'];

            if ($request->file('logo')) {
                $imagePath = $request->file('logo')->store('public/brands');
                $imagePath = explode('/',$imagePath);
                $imagePath[0] = 'storage';
                $brand->logo = join('/' , $imagePath);
            }

            $brand->save();

            return response()->json([
                'data' => $brand,
                'message' => 'برند با موفقیت ویرایش شد'
            ] , 200);


        } catch (\Throwable $th) {

            return response()->json([
                'message' => 'خطایی در حین ویرایش رخ داد'
            ] , 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    /**
     * @OA\Delete(
     * path="/api/admin/brands/{id}",
     * summary="Delete brand",
     * description="Delete one brand",
     * operationId="deleteBrend",
     * tags={"Brands"},
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
        try {
            $brand = Brand::find($id);
            $logoPath = $brand->logo;
            if (isset($logoPath) && File::exists($logoPath)) File::delete($logoPath);
            Brand::destroy($id);
            return response()->json([
                'message' => 'برند با موفقیت حذف شد'
            ] , 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'مشکلی از سمت سرور رخ داده است'
            ] , 500);
        }
    }
}
