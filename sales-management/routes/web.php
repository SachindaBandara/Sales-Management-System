<?php
// Admin Controllers
use App\Http\Controllers\Admin\OrderExportController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\ImageController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ProductController;

// Customer Controllers
use App\Http\Controllers\Customer\CartController;
use App\Http\Controllers\Customer\ProductController as CustomerProductController;
use App\Http\Controllers\Customer\DashboardController as CustomerDashboardController;
use App\Http\Controllers\Customer\OrderController as CustomerOrderController;
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
    Route::get('brands-export', [BrandController::class, 'export'])->name('brands.export');


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

    // Product Management
    Route::resource('products', ProductController::class);

    // Custom Product Routes
    Route::get('products/create', [ProductController::class, 'create'])->name('products.create');

    // Edit a product
    Route::get('products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');

    // Additional Product Actions
    Route::patch('products/{product}/toggle-status', [ProductController::class, 'toggleStatus'])->name('products.toggle-status');

    // Duplicate a product
    Route::post('products/{product}/duplicate', [ProductController::class, 'duplicate'])->name('products.duplicate');

    // Product Statistics
    Route::get('products-statistics', [ProductController::class, 'statistics'])->name('products.statistics');

    // Bulk actions for products
    Route::post('products/bulk-action', [ProductController::class, 'bulkAction'])->name('products.bulk-action');

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

    /*
    |--------------------------------------------------------------------------
    | Order Management Routes
    |--------------------------------------------------------------------------
    |
    | These routes handle all operations related to orders including listing,
    | viewing, updating status, and exporting orders.
    |
    */

    // List all orders with filtering and pagination
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');

    // Show specific order details
    Route::get('/orders/{id}', [OrderController::class, 'show'])->name('orders.show');

    // Update order status
    Route::put('/orders/{id}/status', [OrderController::class, 'updateStatus'])->name('orders.updateStatus');

    // Update payment status
    Route::put('/orders/{id}/payment-status', [OrderController::class, 'updatePaymentStatus'])->name('orders.updatePaymentStatus');

    // Add or update notes for an order
    Route::put('/orders/{id}/notes', [OrderController::class, 'addNotes'])->name('orders.addNotes');

    // Delete an order
    Route::delete('/orders/{id}', [OrderController::class, 'destroy'])->name('orders.destroy');

});




// Customer Routes
Route::middleware(['auth', 'customer'])->prefix('customer')->name('customer.')->group(function () {

    // Customer Dashboard
    Route::get('dashboard', [CustomerDashboardController::class, 'index'])->name('dashboard');
    // Customer Home
    Route::get('home', [CustomerProductController::class, 'index'])->name('home');
    // Show specific product
    Route::get('/products/{product}', [CustomerProductController::class, 'show'])->name('products.show');

       /*
    |--------------------------------------------------------------------------
    | Cart Routes
    |--------------------------------------------------------------------------
    |
    | These routes handle all image operations for products including upload,
    | delete, reorder, and setting primary images.
    |
    */

    // Cart routes
    Route::get('cart', [CartController::class, 'cart'])->name('cart');

    // Cart actions
    Route::post('cart/add/{id}', [CartController::class, 'addToCart'])->name('cart.add');

    // Update cart item quantity
    Route::patch('cart/update', [CartController::class, 'updateCart'])->name('cart.update');

    // Remove item from cart
    Route::delete('cart/remove', [CartController::class, 'removeFromCart'])->name('cart.remove');

    // Clear cart
    Route::delete('cart/clear', [CartController::class, 'clearCart'])->name('cart.clear');

    // Get cart count
    Route::get('cart/count', [CartController::class, 'getCartCount'])->name('cart.count');

     /*
    |--------------------------------------------------------------------------
    | Order Management Routes
    |--------------------------------------------------------------------------
    |
    | These routes handle all image operations for products including upload,
    | delete, reorder, and setting primary images.
    |
    */

    // List all orders
    Route::get('/orders', [CustomerOrderController::class, 'index'])->name('orders');

    // Show specific order details
    Route::get('/orders/{order}', [CustomerOrderController::class, 'show'])->name('orders.show');

    // Show checkout page
    Route::get('/checkout', [CustomerOrderController::class, 'checkout'])->name('checkout');

    // Process order placement
    Route::post('/orders', [CustomerOrderController::class, 'store'])->name('orders.store');

    // Cancel an order
    Route::post('/orders/{order}/cancel', [CustomerOrderController::class, 'cancel'])->name('orders.cancel');


});

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
