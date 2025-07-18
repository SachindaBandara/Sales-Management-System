<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class OrderController extends Controller
{
    /**
     * Display orders management page.
     */
   public function index(Request $request)
    {
        // Validate request parameters
        $query = Order::with(['user', 'items'])
            ->withCount('items');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by payment status
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        // Search by order number or customer name
        if ($request->filled('search')) {
            // Get the search term
            $search = $request->search;

            // Use whereHas to search in related user model
            $query->where(function ($q) use ($search) {
                // Search in order number and user name/email
                $q->where('order_number', 'like', "%{$search}%")
                // orWhereHas to search in related user model
                  ->orWhereHas('user', function ($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%")
                               ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        // Date range filter
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        // Date range filter
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Replace recent() with latest() to sort by created_at in descending order
        $orders = $query->latest()->paginate(15);

        // Get order statistics
        $statistics = $this->getOrderStatistics();

        return Inertia::render('Admin/Orders/Index', [
            'orders' => $orders,
            'statistics' => $statistics,
            'filters' => $request->only(['status', 'payment_status', 'search', 'date_from', 'date_to']),
        ]);
    }

    /**
     * Show specific order details.
     */
    public function show($id)
    {
        // Validate order ID
        $order = Order::with(['user', 'items.product'])
            ->findOrFail($id);

        return Inertia::render('Admin/Orders/Show', [
            'order' => $order
        ]);
    }

    /**
     * Update order status.
     */
    public function updateStatus(Request $request, $id)
    {
        // Validate request parameters
        $request->validate([
            'status' => 'required|in:pending,processing,shipped,delivered,cancelled',
        ]);

        // Find the order by ID
        $order = Order::findOrFail($id);
        // Get the old status before updating
        $oldStatus = $order->status;

        // Start a database transaction
        DB::beginTransaction();

        try {
            // Update the order status
            $order->update(['status' => $request->status]);

            // Handle stock restoration for cancelled orders
            if ($request->status === 'cancelled' && $oldStatus !== 'cancelled') {
                foreach ($order->items as $item) {
                    $product = $item->product;
                    if ($product && $product->track_quantity) {
                        $product->increment('stock_quantity', $item->quantity);
                    }
                }
            }

            // Handle stock reduction for processing orders (if coming from pending)
            if ($request->status === 'processing' && $oldStatus === 'pending') {
                foreach ($order->items as $item) {
                    $product = $item->product;
                    if ($product && $product->track_quantity) {
                        // Check if we have enough stock
                        if ($product->stock_quantity < $item->quantity) {
                            throw new \Exception("Insufficient stock for product: {$product->name}");
                        }
                        $product->decrement('stock_quantity', $item->quantity);
                    }
                }
            }

            // Commit the transaction
            DB::commit();

            // Return success response (redirect back with success message)
            return redirect()->back()->with('success', 'Order status updated successfully');

        } catch (\Exception $e) {
            // Rollback the transaction in case of error
            DB::rollBack();

            // Return error response (redirect back with error message)
            return redirect()->back()->with('error', $e->getMessage() ?: 'Failed to update order status');
        }
    }

    /**
     * Update payment status.
     */
    public function updatePaymentStatus(Request $request, $id)
    {
        // Validate request parameters
        $request->validate([
            'payment_status' => 'required|in:pending,paid,failed,refunded',
        ]);

        try {
            // Find the order by ID
            $order = Order::findOrFail($id);
            
            // Update the payment status
            $order->update(['payment_status' => $request->payment_status]);

            // Return success response (redirect back with success message)
            return redirect()->back()->with('success', 'Payment status updated successfully');

        } catch (\Exception $e) {
            // Return error response (redirect back with error message)
            return redirect()->back()->with('error', 'Failed to update payment status');
        }
    }

    /**
     * Add notes to order.
     */
    public function addNotes(Request $request, $id)
    {
        // Validate request parameters
        $request->validate([
            'notes' => 'nullable|string|max:1000',
        ]);

        // Find the order by ID
        $order = Order::findOrFail($id);
        // Update the notes
        $order->update(['notes' => $request->notes]);

        return response()->json([
            'success' => true,
            'message' => 'Notes updated successfully',
            'order' => $order->fresh()
        ]);
    }

    /**
     * Delete order.
     */
    public function destroy($id)
    {
        // Validate order ID
        $order = Order::findOrFail($id);

        // Start a database transaction
        DB::beginTransaction();

        try {
            // Restore stock if order is being deleted
            if ($order->status !== 'cancelled') {
                foreach ($order->items as $item) {
                    $product = $item->product;
                    if ($product && $product->track_quantity) {
                        $product->increment('stock_quantity', $item->quantity);
                    }
                }
            }
            // Delete the order
            $order->delete();

            // Commit the transaction
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Order deleted successfully'
            ]);

        } catch (\Exception $e) {
            // Rollback the transaction in case of error
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete order'
            ], 500);
        }
    }

    /**
     * Get order statistics.
     */
    private function getOrderStatistics()
    {
        // Get today's date and the start of the current month
        $today = now()->startOfDay();
        // Get the start of the current month
        $thisMonth = now()->startOfMonth();

        return [
            'total_orders' => Order::count(),// Count all orders
            'pending_orders' => Order::where('status', 'pending')->count(),// Count pending orders
            'processing_orders' => Order::where('status', 'processing')->count(),// Count processing orders
            'shipped_orders' => Order::where('status', 'shipped')->count(),// Count shipped orders
            'delivered_orders' => Order::where('status', 'delivered')->count(),// Count delivered orders
            'cancelled_orders' => Order::where('status', 'cancelled')->count(),// Count cancelled orders
            'todays_orders' => Order::whereDate('created_at', $today)->count(),// Count today's orders
            'this_month_orders' => Order::whereDate('created_at', '>=', $thisMonth)->count(),// Count this month's orders
            'total_revenue' => Order::whereNotIn('status', ['cancelled'])->sum('total_amount'),// Total revenue excluding cancelled orders
            'todays_revenue' => Order::whereDate('created_at', $today)
                ->whereNotIn('status', ['cancelled'])
                ->sum('total_amount'),// Today's revenue excluding cancelled orders
            'this_month_revenue' => Order::whereDate('created_at', '>=', $thisMonth)
                ->whereNotIn('status', ['cancelled'])
                ->sum('total_amount'),// This month's revenue excluding cancelled orders
        ];
    }

    /**
     * Export orders to CSV.
     */
    public function export(Request $request)
    {
        $query = Order::with(['user', 'items']);

        // Apply same filters as index
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%")
                               ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $orders = $query->recent()->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="orders-' . now()->format('Y-m-d') . '.csv"',
        ];

        $callback = function () use ($orders) {
            $file = fopen('php://output', 'w');

            // CSV headers
            fputcsv($file, [
                'Order Number',
                'Customer Name',
                'Customer Email',
                'Status',
                'Payment Status',
                'Subtotal',
                'Tax',
                'Total',
                'Items Count',
                'Created At',
            ]);

            // CSV data
            foreach ($orders as $order) {
                fputcsv($file, [
                    $order->order_number,
                    $order->user->name,
                    $order->user->email,
                    $order->status,
                    $order->payment_status,
                    $order->subtotal,
                    $order->tax_amount,
                    $order->total_amount,
                    $order->items->count(),
                    $order->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
