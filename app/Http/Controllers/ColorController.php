<?php

namespace App\Http\Controllers;

use App\Models\Color;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use \Illuminate\Http\JsonResponse;

class ColorController extends Controller
{
    /**
     * @OA\Get(
     *  path="/api/admin/colors",
     *  summary="Get all colors",
     *  description="Get all colors",
     *  operationId="allColors",
     *  tags={"Colors"},
     *  security={ {"bearer_token": {} }},
     *  @OA\Response(
     *    response=200,
     *    description="success",
     *    @OA\JsonContent(
     *       @OA\Property(property="data", type="string", example="[{}, {}]")
     *     )
     *  )
     * )
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(): JsonResponse
    {
        $colors = Color::all();
        $colorsCount = sizeof($colors);
        return response()->json([
            'data' => $colors,
            'message' => $colorsCount > 0 ? "تعداد $colorsCount رنگ دریافت شد" : "هنوز رنگی ایجاد نشده است"
        ] , 200);
    }

    /**
     * @OA\Post(
     * path="/api/admin/colors",
     * summary="Add Color",
     * description="Store one color",
     * operationId="addColor",
     * tags={"Colors"},
     * security={ {"bearer_token": {} }},
     *  @OA\RequestBody(
     *    required=true,
     *    description="Add one color",
     *  @OA\MediaType(
     *    mediaType="application/json",
     *    @OA\Schema(
     *       required={"title", "code"},
     *      @OA\Property(property="title", type="string",  example="white"),
     *      @OA\Property(property="code", type="string",  example="#ffffff"),
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
        try {

            $validator = Validator::make($request->all() , [
                'title' => 'required|unique:colors,title|regex:/^[a-zA-z0-9\-0-9ء-ئ., ؟!:.،\n آابپتثجچحخدذرزژسشصضطظعغفقکگلمنوهیئ\s]+$/' ,
                'code' => 'required|regex:/^[#]{1}\w{3,8}$/' ,
            ]);


            if ($validator->fails()) {
                return response()->json($validator->errors(), 202);
            }
            $color = new Color;
            $color->title = $request['title'];
            $color->code = $request['code'];

            $color->save();

            return response()->json([
                'data' => $color,
                'message' => 'رنگ با موفقیت ایجاد شد'
            ] , 201);


        } catch (\Throwable $th) {

            return response()->json([
                'message' => 'خطایی در حین خیره سازی رخ داد'
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
     *      path="/api/admin/colors/{id}",
     *      summary="Get one color",
     *      description="Get one color with id",
     *      operationId="oneColor",
     *      tags={"Colors"},
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
        $color = Color::find((int)$id);
        if ($color) {
            $colorTitle = $color->title;
            return response()->json([
                "data" => $color,
                'message' => "این رنگ دریافت شد: $colorTitle"
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
     * @OA\Put(
     * path="/api/admin/colors/{id}",
     * summary="Edit color",
     * description="Edit one color",
     * operationId="editColor",
     * tags={"Colors"},
     * security={ {"bearer_token": {} }},
     *  @OA\Parameter(
     *      in="path",
     *      name="id",
     *      required=true,
     *      @OA\Schema(type="string")
     *  ),
     *  @OA\RequestBody(
     *      required=true,
     *      description="Edit color",
     *      @OA\MediaType(
     *          mediaType="application/json",
     *          @OA\Schema(
     *              @OA\Property(property="title", type="string"),
     *              @OA\Property(property="code", type="string"),
     *          ),
     *        example={
     *          "title" : "Edited title",
     *          "code" : "#ffffff",
     *        }
     *      ),
     * ),
     * @OA\Response(
     *    response=200,
     *    description="Success",
     *    @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="ویرایش با موفقیت انجام شد"),
     *    )
     *  )
     * )
     */
    public function update(Request $request, $id): JsonResponse
    {
        $validator = Validator::make($request->all() , [
            'title' => "required|unique:colors,title,$id,id|regex:/^[a-zA-z0-9\-0-9ء-ئ., ؟!:.،\n آابپتثجچحخدذرزژسشصضطظعغفقکگلمنوهیئ\s]+$/" ,
            'code' => 'nullable|regex:/^[#]{1}\w{3,8}$/',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors() , 202);
        }

        try {
            $color = Color::find($id);
            $color->title = $request['title'];
            $color->code = $request['code'];

            $color->save();

            return response()->json([
                'data' => $color,
                'message' => 'رنگ با موفقیت ویرایش شد'
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
     * @return JsonResponse
     */
    /**
     * @OA\Delete(
     * path="/api/admin/colors/{id}",
     * summary="Delete color",
     * description="Delete one color",
     * operationId="deleteColor",
     * tags={"Colors"},
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
    public function destroy(int $id): JsonResponse
    {
        try {
            Color::destroy($id);
            return response()->json([
                'message' => 'رنگ با موفقیت حذف شد'
            ] , 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'مشکلی از سمت سرور رخ داده است'
            ] , 500);
        }
    }
}
