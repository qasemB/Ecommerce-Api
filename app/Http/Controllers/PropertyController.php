<?php

namespace App\Http\Controllers;

use App\Models\Property;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PropertyController extends Controller
{
    /**
     * @OA\Get(
     *  path="/api/admin/categories/{categoryId}/attributes",
     *  summary="category attributes",
     *  description="get all attributes for specific category",
     *  operationId="allCategoryAttributes",
     *  tags={"Attributes"},
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
     * @param \Illuminate\Http\Request $request
     * @return JsonResponse
     */
    /**
     * @OA\Post(
     * path="/api/admin/categories/{categoryId}/attributes",
     * summary="Add attribute",
     * description="Store one Attribute for specific category",
     * operationId="addCategoryAttribute",
     * tags={"Attributes"},
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
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
