<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductColletion;
use App\Http\Resources\ProductResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return ProductColletion
     */
    public function index()
    {
        return new ProductColletion(Product::paginate());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return ProductResource
     */
    public function store(Request $request): ProductResource
    {
        $product = Product::create([
            'name'  => $request->name,
            'slug'  => Str::of($request->name)->slug(),
            'price' => $request->price
        ]);

        return new ProductResource($product);
        //return response()->json(new ProductResource($product), 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return ProductResource
     */
    public function show(int $id): ProductResource
    {
        $product = Product::findOrFail($id);

        return new ProductResource($product);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param  int  $id
     * @return ProductResource
     */
    public function update(Request $request, $id): ProductResource
    {
        $product = Product::findOrFail($id);

        $product->update([
            'name' => $request->name,
            'slug' => Str::of($request->name)->slug(),
            'price' => $request->price,
        ]);

        return new ProductResource($product);
        //return response()->json(new ProductResource($product));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $product = Product::findOrFail($id);

        $product->delete();

        return response()->json(null, 204);
    }
}
