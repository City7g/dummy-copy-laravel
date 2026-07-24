<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $products = QueryBuilder::for(Product::class)
            // ->allowedFilters(
            //     AllowedFilter::custom("rating", new FiltersRating()),
            // )
            ->allowedFilters(
                AllowedFilter::partial("title"),
                AllowedFilter::partial("description"),
                "price",
                "stock",
                "rating",
            )
            ->paginate();

        return ProductResource::collection($products);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductRequest $request)
    {
        $validatedData = $request->validated();

        $product = Product::create($validatedData);

        $product->tags()->sync($validatedData["tags"] ?? []);

        return new ProductResource($product)->response()->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        $product->load("comments");

        return new ProductResource($product);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductRequest $request, Product $product)
    {
        $validatedData = $request->validated();

        $product->update($validatedData);

        $product->tags()->sync($validatedData["tags"] ?? []);

        return new ProductResource($product)->response()->setStatusCode(200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        $product->delete();

        return response()->noContent();
    }
}
