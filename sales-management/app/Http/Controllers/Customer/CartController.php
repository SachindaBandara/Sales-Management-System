<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use Inertia\Inertia;
use Illuminate\Support\Facades\Validator;

class CartController extends Controller
{
    /**
     * Show the cart page.
     */
    public function cart()
    {
        $cart = session()->get('cart', []);
        
        // Calculate cart totals
        $cartTotals = $this->calculateCartTotals($cart);
        
        return Inertia::render('Customer/Cart', [
            'cart' => $cart,
            'cartTotals' => $cartTotals,
            'cartCount' => count($cart)
        ]);
    }

    /**
     * Add product to cart.
     */
    public function addToCart(Request $request, $id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found'
            ], 404);
        }

        if (!$product->is_in_stock) {
            return response()->json([
                'success' => false,
                'message' => 'Product is out of stock'
            ], 400);
        }

        $quantity = $request->input('quantity', 1);
        
        // Validate quantity
        if ($quantity < 1) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid quantity'
            ], 400);
        }

        $cart = session()->get('cart', []);

        if (isset($cart[$id])) {
            $cart[$id]['quantity'] += $quantity;
        } else {
            $cart[$id] = [
                "name" => $product->name,
                "quantity" => $quantity,
                "price" => $product->price,
                "image" => $product->image,
                "product_id" => $product->id
            ];
        }

        session()->put('cart', $cart);
        
        $cartTotals = $this->calculateCartTotals($cart);

        return response()->json([
            'success' => true,
            'message' => 'Product added to cart successfully!',
            'cartCount' => count($cart),
            'cartTotals' => $cartTotals
        ]);
    }

    /**
     * Update cart item quantity.
     */
    public function updateCart(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|string',
            'quantity' => 'required|integer|min:1|max:99'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid input data',
                'errors' => $validator->errors()
            ], 400);
        }

        $cart = session()->get('cart', []);
        
        if (!isset($cart[$request->id])) {
            return response()->json([
                'success' => false,
                'message' => 'Item not found in cart'
            ], 404);
        }

        $cart[$request->id]["quantity"] = $request->quantity;
        session()->put('cart', $cart);
        
        $cartTotals = $this->calculateCartTotals($cart);

        return response()->json([
            'success' => true,
            'message' => 'Cart updated successfully',
            'cartTotals' => $cartTotals,
            'cartCount' => count($cart)
        ]);
    }

    /**
     * Remove item from cart.
     */
    public function removeFromCart(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid item ID'
            ], 400);
        }

        $cart = session()->get('cart', []);
        
        if (!isset($cart[$request->id])) {
            return response()->json([
                'success' => false,
                'message' => 'Item not found in cart'
            ], 404);
        }

        unset($cart[$request->id]);
        session()->put('cart', $cart);
        
        $cartTotals = $this->calculateCartTotals($cart);

        return response()->json([
            'success' => true,
            'message' => 'Product removed successfully',
            'cartTotals' => $cartTotals,
            'cartCount' => count($cart)
        ]);
    }

    /**
     * Clear entire cart.
     */
    public function clearCart()
    {
        session()->forget('cart');
        
        return response()->json([
            'success' => true,
            'message' => 'Cart cleared successfully',
            'cartCount' => 0,
            'cartTotals' => [
                'subtotal' => 0,
                'tax' => 0,
                'total' => 0
            ]
        ]);
    }

    /**
     * Get cart count for header/navigation.
     */
    public function getCartCount()
    {
        $cart = session()->get('cart', []);
        return response()->json([
            'cartCount' => count($cart)
        ]);
    }

    /**
     * Calculate cart totals.
     */
    private function calculateCartTotals($cart)
    {
        $subtotal = 0;
        
        foreach ($cart as $item) {
            $subtotal += $item['price'] * $item['quantity'];
        }
        
        $taxRate = 0.08; // 8% tax rate - adjust as needed
        $tax = $subtotal * $taxRate;
        $total = $subtotal + $tax;
        
        return [
            'subtotal' => round($subtotal, 2),
            'tax' => round($tax, 2),
            'total' => round($total, 2),
            'taxRate' => $taxRate
        ];
    }
}