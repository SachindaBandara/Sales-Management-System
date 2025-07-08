<?php

use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\ImageController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Customer\CartController;
use App\Http\Controllers\Customer\ProductController as CustomerProductController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Customer\DashboardController as CustomerDashboardController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
})->name('home');

// Redirect after login based on role
Route::middleware('auth')->get('/dashboard', function () {
    $user = \Illuminate\Support\Facades\Auth::user();
    // Ensure $user is an instance of App\Models\User
    if ($user instanceof \App\Models\User && $user->isAdmin()) {
        return redirect()->route('admin.dashboard');
    }
    return redirect()->route('customer.dashboard');
})->name('dashboard');



// Admin Routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    // User Management
    Route::resource('users', UserController::class);
    Route::patch('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');

    // Brand Management
    Route::resource('brands', BrandController::class);
    Route::patch('brands/{brand}/toggle-status', [BrandController::class, 'toggleStatus'])->name('brands.toggle-status');

    // Category Management
    Route::resource('categories', CategoryController::class);
    Route::patch('categories/{category}/toggle-status', [CategoryController::class, 'toggleStatus'])->name('categories.toggle-status');

    /*
    |--------------------------------------------------------------------------
    | Product Management Routes
    |--------------------------------------------------------------------------
    |
    | These routes handle all operations for products including upload,
    | delete, edit, view, bulk action and statistic .
    |
    */
    Route::resource('products', ProductController::class);
    // Custom Product Routes
    Route::get('products/create', [ProductController::class, 'create'])->name('products.create');
    Route::get('products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');

    // Additional Product Actions
    Route::patch('products/{product}/toggle-status', [ProductController::class, 'toggleStatus'])
        ->name('products.toggle-status');

    Route::post('products/{product}/duplicate', [ProductController::class, 'duplicate'])
        ->name('products.duplicate');

    Route::get('products-statistics', [ProductController::class, 'statistics'])
        ->name('products.statistics');

    Route::post('products/bulk-action', [ProductController::class, 'bulkAction'])
        ->name('products.bulk-action');

    /*
    |--------------------------------------------------------------------------
    | Product Image Management Routes
    |--------------------------------------------------------------------------
    |
    | These routes handle all image operations for products including upload,
    | delete, reorder, and setting primary images.
    |
    */
    Route::prefix('products/{product}/images')->name('products.images.')->group(function () {
        // Get all images for a product
        Route::get('/', [ImageController::class, 'index'])->name('index');

        // Upload new images
        Route::post('/upload', [ImageController::class, 'upload'])->name('upload');

        // Delete a specific image
        Route::delete('/delete', [ImageController::class, 'delete'])->name('delete');

        // Delete all images
        Route::delete('/delete-all', [ImageController::class, 'deleteAll'])->name('delete-all');

        // Reorder images
        Route::patch('/reorder', [ImageController::class, 'reorder'])->name('reorder');

        // Set primary image
        Route::patch('/set-primary', [ImageController::class, 'setPrimary'])->name('set-primary');
    });
});



// Customer Routes
Route::middleware(['auth', 'customer'])->prefix('customer')->name('customer.')->group(function () {
    Route::get('dashboard', [CustomerDashboardController::class, 'index'])->name('dashboard');
    Route::get('home', [CustomerProductController::class, 'index'])->name('home');
    Route::get('/products/{product}', [CustomerProductController::class, 'show'])->name('products.show');

    // Cart routes
    Route::get('cart', [CartController::class, 'cart'])->name('cart');
    Route::post('cart/add/{id}', [CartController::class, 'addToCart'])->name('cart.add');
    Route::patch('cart/update', [CartController::class, 'updateCart'])->name('cart.update');
    Route::delete('cart/remove', [CartController::class, 'removeFromCart'])->name('cart.remove');
    Route::delete('cart/clear', [CartController::class, 'clearCart'])->name('cart.clear');
    Route::get('cart/count', [CartController::class, 'getCartCount'])->name('cart.count');
});

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
