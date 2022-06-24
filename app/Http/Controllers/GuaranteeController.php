<?php

namespace App\Http\Controllers;

use App\Models\Guarantee;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class GuaranteeController extends Controller
{
    /**
     * @OA\Get(
     *  path="/api/admin/guarantees",
     *  summary="Get all guarantees",
     *  description="Get all guarantees",
     *  operationId="allGuarantees",
     *  tags={"Guarantees"},
     *  security={ {"bearer_token": {} }},
     *  @OA\Response(
     *    response=200,
     *    description="success",
     *    @OA\JsonContent(
     *       @OA\Property(property="data", type="string", example="[{...}, {...}]")
     *     )
     *  )
     * )
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $guarantees = Guarantee::all();
        $guaranteesCount = sizeof($guarantees);
        return response()->json([
            'data' => $guarantees,
            'message' => $guaranteesCount > 0 ? "تعداد $guaranteesCount ضمانت دریافت شد" : "هنوز ضمانتی ایجاد نشده است"
        ] , 200);
    }

    /**
     * @OA\Post(
     * path="/api/admin/guarantees",
     * summary="Add Guarantee",
     * description="Store one guarantee",
     * operationId="addGuarantee",
     * tags={"Guarantees"},
     * security={ {"bearer_token": {} }},
     *  @OA\RequestBody(
     *    required=true,
     *    description="Add one guarantee",
     *  @OA\MediaType(
     *    mediaType="application/json",
     *    @OA\Schema(
     *       required={"title"},
     *      @OA\Property(property="title", type="string",  example="test guarantee"),
     *      @OA\Property(property="descriptions", type="string",  example="some description about this record..."),
     *      @OA\Property(property="length", type="number",  example="1"),
     *      @OA\Property(property="length_unit", type="string",  example="year"),
     *    )
     *   ),
     * ),
     * @OA\Response(
     *    response=201,
     *    description="success",
     *    @OA\JsonContent(
     *       @OA\Property(property="data", type="string", example="{}"),
     *        )
     *     )
     * )
     */
    public function store(Request $request): JsonResponse
    {
        try {

            $validator = Validator::make($request->all() , [
                'title' => 'required|regex:/^[a-zA-z0-9\-0-9ء-ئ., ؟!:.،\n آابپتثجچحخدذرزژسشصضطظعغفقکگلمنوهیئ\s]+$/' ,
                'descriptions' => 'nullable|regex:/^[a-zA-z0-9\-0-9ء-ئ., ؟!:.،\n آابپتثجچحخدذرزژسشصضطظعغفقکگلمنوهیئ\s]+$/' ,
                'length' => 'nullable|numeric' ,
                'length_unit' => 'nullable|regex:/^[a-zA-z0-9\-0-9ء-ئ., ؟!:.،\n آابپتثجچحخدذرزژسشصضطظعغفقکگلمنوهیئ\s]+$/' ,
            ]);


            if ($validator->fails()) {
                return response()->json($validator->errors(), 202);
            }
            $guarantee = new Guarantee;
            $guarantee->title = $request['title'];
            $guarantee->descriptions = $request['descriptions'];
            $guarantee->length = $request['length'];
            $guarantee->length_unit = $request['length_unit'];

            $guarantee->save();

            return response()->json([
                'data' => $guarantee,
                'message' => 'ضمانت با موفقیت ایجاد شد'
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
     *      path="/api/admin/guarantees/{id}",
     *      summary="Get one guarantee",
     *      description="Get one guarantee with id",
     *      operationId="oneGuarantee",
     *      tags={"Guarantees"},
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
    public function show($id): JsonResponse
    {
        $guarantee = Guarantee::find((int)$id);
        if ($guarantee) {
            $guaranteeTitle = $guarantee->title;
            return response()->json([
                "data" => $guarantee,
                'message' => "این ضمانت دریافت شد: $guaranteeTitle"
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
     * path="/api/admin/guarantees/{id}",
     * summary="Edit guarantee",
     * description="Edit one guarantee",
     * operationId="editGuarantee",
     * tags={"Guarantees"},
     * security={ {"bearer_token": {} }},
     *  @OA\Parameter(
     *      in="path",
     *      name="id",
     *      required=true,
     *      @OA\Schema(type="string")
     *  ),
     *  @OA\RequestBody(
     *      required=true,
     *      description="Edit guarantee",
     *      @OA\MediaType(
     *          mediaType="application/json",
     *          @OA\Schema(
     *              @OA\Property(property="title", type="string"),
     *              @OA\Property(property="descriptions", type="string"),
     *              @OA\Property(property="length", type="number"),
     *              @OA\Property(property="length_unit", type="string"),
     *          ),
     *        example={
     *          "title" : "Edited title",
     *          "descriptions" : "Edited description",
     *          "length" : "10",
     *          "length_unit" : "month",
     *        }
     *      ),
     * ),
     * @OA\Response(
     *    response=200,
     *    description="Success",
     *    @OA\JsonContent(
     *       @OA\Property(property="data", type="string", example="{...}"),
     *       @OA\Property(property="message", type="string", example="ویرایش با موفقیت انجام شد"),
     *    )
     *  )
     * )
     */
    public function update(Request $request, $id): JsonResponse
    {
        $validator = Validator::make($request->all() , [
            'title' => 'required|regex:/^[a-zA-z0-9\-0-9ء-ئ., ؟!:.،\n آابپتثجچحخدذرزژسشصضطظعغفقکگلمنوهیئ\s]+$/' ,
            'descriptions' => 'nullable|regex:/^[a-zA-z0-9\-0-9ء-ئ., ؟!:.،\n آابپتثجچحخدذرزژسشصضطظعغفقکگلمنوهیئ\s]+$/' ,
            'length' => 'nullable|numeric' ,
            'length_unit' => 'nullable|regex:/^[a-zA-z0-9\-0-9ء-ئ., ؟!:.،\n آابپتثجچحخدذرزژسشصضطظعغفقکگلمنوهیئ\s]+$/' ,
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors() , 202);
        }

        try {
            $guarantee = Guarantee::find($id);
            $guarantee->title = $request['title'];
            $guarantee->descriptions = $request['descriptions'];
            $guarantee->length = $request['length'];
            $guarantee->length_unit = $request['length_unit'];

            $guarantee->save();

            return response()->json([
                'data' => $guarantee,
                'message' => 'ضمانت با موفقیت ویرایش شد'
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
     * path="/api/admin/guarantees/{id}",
     * summary="Delete guarantee",
     * description="Delete one guarantee",
     * operationId="deleteGuarantee",
     * tags={"Guarantees"},
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
        try {
            Guarantee::destroy($id);
            return response()->json([
                'message' => 'ضمانت با موفقیت حذف شد'
            ] , 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'مشکلی از سمت سرور رخ داده است'
            ] , 500);
        }
    }
}
