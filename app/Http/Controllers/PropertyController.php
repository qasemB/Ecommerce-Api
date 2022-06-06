<?php

namespace App\Http\Controllers;

use App\Models\Property;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class PropertyController extends Controller
{
    /**
     * @OA\Get(
     *  path="/api/admin/categories/{categoryId}/attributes",
     *  summary="Category attributes",
     *  description="get all attributes for specific category",
     *  operationId="allCategoryAttributes",
     *  tags={"Category Attributes"},
     *  security={ {"bearer_token": {} }},
     *  @OA\Parameter(
     *      in="path",
     *      name="categoryId",
     *      required=true,
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
     * @param $categoryId
     * @return JsonResponse
     */
    public function index($categoryId)
    {
        try {
            $properties = Property::where('category_id', (int)$categoryId)->get();
            $count = sizeof($properties);
            return response()->json([
                'data' => $properties,
                'message' => $count > 0 ? ("تعداد $count  ویژگی برای گروه $categoryId پیدا شد ") : (" فعلا ویژگی برای گروه $categoryId ایجاد نشده است ")
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    /**
     * @OA\Post(
     * path="/api/admin/categories/{categoryId}/attributes",
     * summary="Add attribute",
     * description="Store one Attribute for specific category",
     * operationId="addCategoryAttribute",
     * tags={"Category Attributes"},
     * security={ {"bearer_token": {} }},
     *  @OA\Parameter(
     *      in="path",
     *      name="categoryId",
     *      required=true,
     *      @OA\Schema(type="number")
     *  ),
     *  @OA\RequestBody(
     *    required=true,
     *    description="Add one attribute for spcific category",
     *  @OA\MediaType(
     *    mediaType="application/json",
     *    @OA\Schema(
     *       required={"title", "unit", "in_filter"},
     *      @OA\Property(property="title", type="string",  example="description text ..."),
     *      @OA\Property(property="unit", type="string",  example="عدد"),
     *      @OA\Property(property="in_filter", type="number", example="1"),
     *    )
     *   ),
     * ),
     * @OA\Response(
     *    response=201,
     *    description="success",
     *    @OA\JsonContent(
     *       @OA\Property(property="data", type="string", example="{id: ..., ...}"),
     *       @OA\Property(property="message", type="string", example="suucess..."),
     *    )
     * )
     * )
     */
    public function store(Request $request, $categoryId)
    {
        try {
            $validator = Validator::make($request->all() , [
                'title' => 'required|regex:/^[a-zA-z0-9\-0-9ء-ئ., ؟!:.،\n آابپتثجچحخدذرزژسشصضطظعغفقکگلمنوهیئ\s]+$/' ,
                'unit' => 'required|regex:/^[a-zA-z0-9\-0-9ء-ئ., ؟!:.،\n آابپتثجچحخدذرزژسشصضطظعغفقکگلمنوهیئ\s]+$/' ,
                'in_filter' => 'required|numeric' ,
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 202);
            }


            $property = new Property;
            $property->category_id = $categoryId;
            $property->title = $request['title'];
            $property->unit = $request['unit'];
            $property->in_filter = $request['in_filter'];


            $property->save();

            return response()->json([
                'data' => $property,
                'message' => 'ویژگی با موفقیت ایجاد شد'
            ] , 201);


        } catch (\Throwable $th) {

            return response()->json([
                'message' => $th
            ] , 500);

        }
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return Response
     */
    /**
     * @OA\Get(
     *      path="/api/admin/categories/attributes/{id}",
     *      summary="Get one attribute",
     *      description="Get one attribute with id",
     *      operationId="oneAttribute",
     *      tags={"Category Attributes"},
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
     *       @OA\Property(property="data", type="string", example="{}")
     *     )
     *   )
     * )
     */
    public function show($id)
    {
        $property = Property::find($id);
        if ($property) {
            return response()->json([
                "data" => $property
            ] , 200);
        }else{
            return response()->json([
                "message" => "هیچ موردی یافت نشد"
            ] , 202);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $id
     * @return Response
     */
    /**
     * @OA\Put(
     * path="/api/admin/categories/attributes/{id}",
     * summary="Edit category attributes ",
     * description="Edit one attributes",
     * operationId="editAttribute",
     * tags={"Category Attributes"},
     * security={ {"bearer_token": {} }},
     *  @OA\Parameter(
     *      in="path",
     *      name="id",
     *      required=true,
     *      @OA\Schema(type="string")
     *  ),
     *  @OA\RequestBody(
     *      required=true,
     *      description="edit attribute",
     *      @OA\MediaType(
     *          mediaType="application/json",
     *          @OA\Schema(
     *              @OA\Property(property="title", type="string"),
     *              @OA\Property(property="unit", type="string"),
     *              @OA\Property(property="in_filter", type="number"),
     *          ),
     *        example={
     *          "title" : "example title edited",
     *          "unit" : "عدد",
     *          "in_filter" : "1",
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
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all() , [
            'title' => 'required|regex:/^[a-zA-z0-9\-0-9ء-ئ., ؟!:.،\n آابپتثجچحخدذرزژسشصضطظعغفقکگلمنوهیئ\s]+$/' ,
            'unit' => 'required|regex:/^[a-zA-z0-9\-0-9ء-ئ., ؟!:.،\n آابپتثجچحخدذرزژسشصضطظعغفقکگلمنوهیئ\s]+$/' ,
            'in_filter' => 'required|numeric' ,
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors() , 202);
        }


        try {

            $property = Property::find($id);

            if (!isset($property)) return response()->json([
                'message' => 'هیچ ویژگی یافت نشد'
            ] , 202);

            $property->title = $request['title'];
            $property->unit = $request['unit'];
            $property->in_filter = $request['in_filter'];

            $property->save();

            return response()->json([
                'data'=> $property,
                'message' => 'ویژگی با موفقیت ویرایش شد'
            ] , 200);

        } catch (\Throwable $th) {

            return response()->json([
                'message' => $th
            ] , 500);

        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return Response
     */
    /**
     * @OA\Delete(
     * path="/api/admin/categories/attributes/{id}",
     * summary="Delete category attribute",
     * description="Delete one category attribute",
     * operationId="deleteCategoryAttr",
     * tags={"Category Attributes"},
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
    public function destroy($id)
    {
        try {
            Property::destroy($id);
            return response()->json([
                'deletedId' => $id,
                'message' => 'ویژگی با موفقیت حذف شد'
            ] , 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th
            ] , 500);
        }
    }
}
