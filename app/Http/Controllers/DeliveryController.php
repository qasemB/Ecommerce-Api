<?php

namespace App\Http\Controllers;

use App\Models\Delivery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DeliveryController extends Controller
{
    /**
     * @OA\Get(
     *  path="/api/admin/deliveries",
     *  summary="Get all deliveries",
     *  description="Get all deliveries",
     *  operationId="allDeliveries",
     *  tags={"Deliveries"},
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
    public function index()
    {
        $deliveries = Delivery::all();
        $deliveriesCount = sizeof($deliveries);
        return response()->json([
            'data' => $deliveries,
            'message' => $deliveriesCount > 0 ? "تعداد $deliveriesCount روش ارسال دریافت شد" : "هنوز روش ارسال ایجاد نشده است"
        ] , 200);
    }

    /**
     * @OA\Post(
     * path="/api/admin/deliveries",
     * summary="Add deliveries code",
     * description="Store one deliveries code",
     * operationId="addDelivery",
     * tags={"Deliveries"},
     * security={ {"bearer_token": {} }},
     *  @OA\RequestBody(
     *    required=true,
     *    description="Add one delivery",
     *  @OA\MediaType(
     *    mediaType="application/json",
     *    @OA\Schema(
     *       required={"title", "amount"},
     *      @OA\Property(property="title", type="string",  example="delivery test"),
     *      @OA\Property(property="amount", type="string",  example="10000"),
     *      @OA\Property(property="time", type="number",  example="5"),
     *      @OA\Property(property="time_unit", type="string",  example="روز"),
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
    public function store(Request $request)
    {

        $validator = Validator::make($request->all() , [
            'title' => 'required|unique:deliveries,title|regex:/^[a-zA-z0-9\-0-9ء-ئ., ؟!:.،\n آابپتثجچحخدذرزژسشصضطظعغفقکگلمنوهیئ\s]+$/' ,
            'amount' => 'required|regex:/^[0-9]+$/' ,
            'time' => 'regex:/^[0-9]+$/' ,
            'time_unit' => 'regex:/^[a-zA-z0-9\-0-9ء-ئ., ؟!:.،\n آابپتثجچحخدذرزژسشصضطظعغفقکگلمنوهیئ\s]+$/' ,
        ]);


        if ($validator->fails()) {
            return response()->json($validator->errors(), 202);
        }

        $delivery = new Delivery;
        $delivery->title = $request['title'];
        $delivery->amount = $request['amount'];
        $delivery->time = $request['time'];
        $delivery->time_unit = $request['time_unit'];
        $delivery->save();

        return response()->json([
            'data' => $delivery,
            'message' => 'روش ارسال با موفقیت ایجاد شد'
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
     *      path="/api/admin/deliveries/{id}",
     *      summary="Get one delivery",
     *      description="Get one delivery with id",
     *      operationId="oneDelivery",
     *      tags={"Deliveries"},
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
    public function show($id)
    {
        $delivery = Delivery::find($id);
        return response()->json([
            'data' => $delivery,
            'message' => 'دریافت  با موفقیت انجام شد'
        ], 200);
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
     * path="/api/admin/deliveries/{id}",
     * summary="Edit delivery",
     * description="Edit one delivery",
     * operationId="editDelivery",
     * tags={"Deliveries"},
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
     *              @OA\Property(property="amount", type="string"),
     *              @OA\Property(property="time", type="number"),
     *              @OA\Property(property="time_unit", type="string"),
     *          ),
     *        example={
     *          "title" : "edited delivery",
     *          "amount" : "10001",
     *          "time" : "4",
     *          "time_unit" : "روز",
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
            'title' => "required|unique:deliveries,title,$id,id|regex:/^[a-zA-z0-9\-0-9ء-ئ., ؟!:.،\n آابپتثجچحخدذرزژسشصضطظعغفقکگلمنوهیئ\s]+$/" ,
            'amount' => 'required|regex:/^[0-9]+$/',
            'time' => 'nullable|regex:/^[0-9]+$/' ,
            'time_unit' => 'nullable|regex:/^[a-zA-z0-9\-0-9ء-ئ., ؟!:.،\n آابپتثجچحخدذرزژسشصضطظعغفقکگلمنوهیئ\s]+$/' ,
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 202);
        }


        $delivery = Delivery::find($id);
        $delivery->title = $request['title'];
        $delivery->amount = $request['amount'];
        $delivery->time = $request['time'];
        $delivery->time_unit = $request['time_unit'];

        return response()->json([
            'data'=> $delivery,
            'message' => 'روش ارسال با موفقیت ویرایش شد'
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
     * path="/api/admin/deliveries/{id}",
     * summary="Delete deliveries",
     * description="Delete one delivery",
     * operationId="deleteDelivery",
     * tags={"Deliveries"},
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
        Delivery::destroy($id);
        return response()->json([
            'message' => 'روش ارسال با موفقیت حذف شد'
        ] , 200);
    }
}
