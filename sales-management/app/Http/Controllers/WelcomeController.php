<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Brand;
use App\Models\Category;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Validator;


class WelcomeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $products = Product::query()
            ->with(['brand', 'category'])
            ->active()
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
            })
            ->when($request->category, function ($query, $category) {
                $query->whereHas('category', function ($q) use ($category) {
                    $q->where('slug', $category);
                });
            })
            ->when($request->brand, function ($query, $brand) {
                $query->whereHas('brand', function ($q) use ($brand) {
                    $q->where('slug', $brand);
                });
            })
            ->when($request->min_price, function ($query, $minPrice) {
                $query->where('price', '>=', $minPrice);
            })
            ->when($request->max_price, function ($query, $maxPrice) {
                $query->where('price', '<=', $maxPrice);
            })
            ->when($request->sort, function ($query, $sort) {
                switch ($sort) {
                    case 'price_low':
                        $query->orderBy('price', 'asc');
                        break;
                    case 'price_high':
                        $query->orderBy('price', 'desc');
                        break;
                    case 'name':
                        $query->orderBy('name', 'asc');
                        break;
                    default:
                        $query->orderBy('created_at', 'desc');
                }
            })
            ->paginate(12)
            ->withQueryString();

        $products->getCollection()->transform(function ($product) {
            $product->image_urls = $product->image_urls; // Triggers the accessor
            $product->first_image_url = $product->first_image_url; // Triggers the accessor
            return $product;
        });

        $brands = Brand::active()->withCount('products')->having('products_count', '>', 0)->get();
        $categories = Category::active()->withCount('products')->having('products_count', '>', 0)->get();

        return Inertia::render('Welcome', [
            'products' => $products,
            'brands' => $brands,
            'categories' => $categories,
            'filters' => $request->only(['search', 'category', 'brand', 'min_price', 'max_price', 'sort']),
        ]);

    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        return Inertia::render('Show', [
        'product' => $product->load(['brand', 'category']),
    ]);
    }

}
