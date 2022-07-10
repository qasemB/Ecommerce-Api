<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    /**
     * @OA\Get(
     *  path="/api/admin/categories",
     *  summary="categories",
     *  description="get all categories",
     *  operationId="allCategories",
     *  tags={"Categories"},
     *  security={ {"bearer_token": {} }},
     *  @OA\Parameter(
     *      in="query",
     *      name="parent",
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
        try {
            $parent = $request->input("parent");
            if ($parent != null) $parent = (int) $parent;
            $categories = Category::where('parent_id',$parent)->get();
            return response()->json([
                'data' => $categories
            ] , 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th
            ] , 500);
        }
    }




    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return Response
     */
    /**
     * @OA\Post(
     * path="/api/admin/categories",
     * summary="add category",
     * description="store one category",
     * operationId="addCategory",
     * tags={"Categories"},
     * security={ {"bearer_token": {} }},
     *  @OA\RequestBody(
     *    required=true,
     *    description="add one category",
     *  @OA\MediaType(
     *    mediaType="multipart/form-data",
     *    @OA\Schema(
     *       required={"title"},
     *      @OA\Property(property="title", type="string",  example="category test"),
     *      @OA\Property(property="descriptions", type="string",  example="description text ..."),
     *      @OA\Property(property="parent_id", type="string",  example="1"),
     *      @OA\Property(property="is_active", type="number", example="1"),
     *      @OA\Property(property="show_in_menu", type="number", example="1"),
     *      @OA\Property(property="image", description="file to upload",type="file"),
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
        try {

            $validator = Validator::make($request->all() , [
                'title' => 'required|unique:categories,title|regex:/^[a-zA-z0-9\-0-9ء-ئ., ؟!:.،\n آابپتثجچحخدذرزژسشصضطظعغفقکگلمنوهیئ\s]+$/' ,
                'descriptions' => 'nullable|regex:/^[a-zA-z0-9\-0-9ء-ئ., ؟!:.،\n آابپتثجچحخدذرزژسشصضطظعغفقکگلمنوهیئ\s]+$/' ,
                'parent_id ' => 'nullable|numeric' ,
                'show_in_menu ' => 'nullable|numeric' ,
                'image' => 'nullable|image|max:500' ,
                'is_active' => 'numeric' ,
            ]);


            if ($validator->fails()) {
                return response()->json($validator->errors(), 202);
            }
            $category = new Category;
            $category->title = $request['title'];
            $category->descriptions = $request['descriptions'];
            $category->parent_id = $request['parent_id'];
            $category->is_active = $request['is_active'];
            $category->show_in_menu = $request['show_in_menu'];

            if ($request->file('image')) {
                $imgpath = Storage::disk('public')->put('images/categories', $request->file('image'));
                $category->image = $imgpath;
            }

            $category->save();

            return response()->json([
                'message' => 'گروه با موفقیت ایجاد شد'
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
     * @param  int  $id
     * @return Response
     */
    /**
     * @OA\Get(
     *      path="/api/admin/categories/{id}",
     *      summary="one categorie",
     *      description="get one categories with id",
     *      operationId="oneCategory",
     *      tags={"Categories"},
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
     *  )
     * )
     */
    public function show($id)
    {
        $category = Category::find($id);
        if ($category) {
            return response()->json([
                "data" => $category
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
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return Response
     */
    /**
     * @OA\Put(
     * path="/api/admin/categories/{id}",
     * summary="edit category",
     * description="edit one category",
     * operationId="editCategory",
     * tags={"Categories"},
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
     *              @OA\Property(property="descriptions", type="string"),
     *              @OA\Property(property="parent_id", type="number"),
     *              @OA\Property(property="show_in_menu", type="number"),
     *              @OA\Property(property="is_active", type="number"),
     *          ),
     *        example={
     *          "title" : "example title",
     *          "descriptions" : "",
     *          "parent_id" : "",
     *          "is_active" : 1,
     *          "show_in_menu" : 1
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
            'descriptions' => 'nullable|regex:/^[a-zA-z0-9\-0-9ء-ئ., ؟!:.،\n آابپتثجچحخدذرزژسشصضطظعغفقکگلمنوهیئ\s]+$/' ,
            'parent_id ' => 'nullable|numeric' ,
            'show_in_menu ' => 'nullable|numeric' ,
            'image' => 'nullable|image|max:500' ,
            'is_active' => 'nullable|numeric' ,
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors() , 202);
        }


        try {

            $category = Category::find($id);
            $category->title = $request['title'];
            $category->descriptions = $request['descriptions'];
            $category->parent_id = $request['parent_id'];
            $category->is_active = $request['is_active'];
            $category->show_in_menu = $request['show_in_menu'];

            if ($request->file('image')) {
                $imagePath = $request->file('image')->store('public/categories');
                $imagePath = explode('/',$imagePath);
                $imagePath[0] = 'storage';
                $category->image = join('/' , $imagePath);
            }

            $category->save();

            return response()->json([
                'message' => 'گروه با موفقیت ویرایش شد'
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
     * @param  int  $id
     * @return Response
     */
    /**
     * @OA\Delete(
     * path="/api/admin/categories/{id}",
     * summary="delete category",
     * description="delete one category",
     * operationId="deleteCategory",
     * tags={"Categories"},
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
            $category = Category::find($id);
            $imagePath = $category->image;
            if (isset($imagePath) && File::exists($imagePath)) File::delete($imagePath);
            Category::destroy($id);
            return response()->json([
                'message' => 'گروه با موفقیت حذف شد'
            ] , 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th
            ] , 500);
        }
    }
}
