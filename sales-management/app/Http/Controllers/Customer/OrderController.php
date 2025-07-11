<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class OrderController extends Controller
{
    use AuthorizesRequests;
    /**
     * Display customer's orders.
     */
    public function index()
    {
        $orders = Order::with(['items.product'])
            ->forUser(Auth::id())
            ->latest()
            ->paginate(10);

        return Inertia::render('Customer/Orders/Index', [
            'orders' => $orders
        ]);
    }

    /**
     * Show specific order details.
     */
    public function show(Order $order)
    {
        $this->authorize('view', $order);

        $order->load(['items.product', 'user']);

        return Inertia::render('Customer/Orders/Show', [
            'order' => $order
        ]);
    }

    /**
     * Show checkout page.
     */
    public function checkout()
    {
        // capture the cart from session
        $cart = session()->get('cart', []);

        // Check if cart is empty
        if (empty($cart)) {
            return redirect()->route('customer.cart')
                ->with('error', 'Your cart is empty');
        }

        // Calculate cart totals
        $cartTotals = $this->calculateCartTotals($cart);

        return Inertia::render('Customer/Checkout', [
            'cart' => $cart,
            'cartTotals' => $cartTotals,
            'user' => Auth::user()
        ]);
    }

    /**
     * Process order placement.
     */
    public function store(Request $request)
    {
        // Validate request data
        $validator = Validator::make($request->all(), [
            'billing_address' => 'required|array',
            'billing_address.first_name' => 'required|string|max:255',
            'billing_address.last_name' => 'required|string|max:255',
            'billing_address.email' => 'required|email|max:255',
            'billing_address.phone' => 'required|string|max:20',
            'billing_address.address_line_1' => 'required|string|max:255',
            'billing_address.address_line_2' => 'nullable|string|max:255',
            'billing_address.city' => 'required|string|max:255',
            'billing_address.state' => 'required|string|max:255',
            'billing_address.postal_code' => 'required|string|max:20',
            'billing_address.country' => 'required|string|max:255',
            'shipping_address' => 'required|array',
            'shipping_same_as_billing' => 'boolean',
            'payment_method' => 'required|string|in:cash_on_delivery,card,paypal',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Check if validation fails
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Capture the cart from session
        $cart = session()->get('cart', []);

        // Check if cart is empty
        if (empty($cart)) {
            return redirect()->route('customer.cart')
                ->with('error', 'Your cart is empty');
        }

        try {
            // Start database transaction
            DB::beginTransaction();

            // Validate cart items and stock
            $validatedCart = $this->validateCartItems($cart);
            if (!$validatedCart['success']) {
                return redirect()->route('customer.cart')
                    ->with('error', $validatedCart['message']);
            }

            // Calculate totals
            $cartTotals = $this->calculateCartTotals($cart);

            // Handle shipping address
            $shippingAddress = $request->shipping_same_as_billing
                ? $request->billing_address
                : $request->shipping_address;

            // Create order
            $order = Order::create([
                'order_number' => Order::generateOrderNumber(),
                'user_id' => Auth::id(),
                'status' => 'pending',
                'subtotal' => $cartTotals['subtotal'],
                'tax_amount' => $cartTotals['tax'],
                'shipping_amount' => 0, // You can add shipping calculation here
                'discount_amount' => 0, // You can add discount calculation here
                'total_amount' => $cartTotals['total'],
                'payment_method' => $request->payment_method,
                'payment_status' => $request->payment_method === 'cash_on_delivery' ? 'pending' : 'pending',
                'billing_address' => $request->billing_address,
                'shipping_address' => $shippingAddress,
                'notes' => $request->notes,
            ]);

            // Create order items and update stock
            foreach ($cart as $productId => $item) {
                $product = Product::find($productId);

                // Create order item
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $productId,
                    'product_name' => $product->name,
                    'product_sku' => $product->sku,
                    'product_price' => $product->price,
                    'quantity' => $item['quantity'],
                    'total_price' => $product->price * $item['quantity'],
                    'product_snapshot' => [
                        'name' => $product->name,
                        'sku' => $product->sku,
                        'price' => $product->price,
                        'image' => $product->first_image_url,
                        'description' => $product->short_description,
                    ],
                ]);

                // Update stock
                $product->decreaseStock($item['quantity']);
            }

            // Clear cart
            session()->forget('cart');

            // Commit transaction
            DB::commit();

            return redirect()->route('customer.orders.show', $order)
                ->with('success', 'Order placed successfully!');

        } catch (\Exception $e) {
            // Rollback transaction on error
            DB::rollBack();

            return redirect()->back()
                ->with('error', 'Failed to place order. Please try again.')
                ->withInput();
        }
    }

    /**
     * Cancel an order.
     */
    public function cancel(Order $order)
    {
        // Authorize the action
        $this->authorize('update', $order);

        // Check if the order can be cancelled
        if (!$order->canBeCancelled()) {
            return redirect()->back()
                ->with('error', 'This order cannot be cancelled');
        }

        try {
            // Start database transaction
            DB::beginTransaction();

            // Update order status
            $order->update(['status' => 'cancelled']);

            // Restore stock
            foreach ($order->items as $item) {
                $item->product->increaseStock($item->quantity);
            }

            // Delete order items
            $order->delete();

            // Commit transaction
            DB::commit();

            return redirect()->back()
                ->with('success', 'Order cancelled successfully');

        } catch (\Exception $e) {
            // Rollback transaction on error
            DB::rollBack();

            return redirect()->back()
                ->with('error', 'Failed to cancel order');
        }
    }

    /**
     * Validate cart items.
     */
    private function validateCartItems($cart)
    {
        // Check if cart is empty
        foreach ($cart as $productId => $item) {

            $product = Product::find($productId);

            // Check if product exists and is in stock
            if (!$product) {
                return [
                    'success' => false,
                    'message' => "Product '{$item['name']}' not found"
                ];
            }

            // Check if product is in stock
            if (!$product->is_in_stock) {
                return [
                    'success' => false,
                    'message' => "Product '{$product->name}' is out of stock"
                ];
            }

            // Check if product quantity exceeds stock
            if ($product->track_quantity && $product->stock_quantity < $item['quantity']) {
                return [
                    'success' => false,
                    'message' => "Insufficient stock for '{$product->name}'. Available: {$product->stock_quantity}"
                ];
            }
        }

        return ['success' => true];
    }

    /**
     * Calculate cart totals.
     */
    private function calculateCartTotals($cart)
    {
        $subtotal = 0;

        // Calculate subtotal
        foreach ($cart as $item) {
            $subtotal += $item['price'] * $item['quantity'];
        }

        $taxRate = 0.08; // 8% tax rate
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
