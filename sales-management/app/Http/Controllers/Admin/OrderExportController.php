<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Exports\OrdersExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Response;

class OrderExportController extends Controller
{
    /**
     * Export orders to Excel.
     */
    public function export(Request $request)
    {
        try {
            // Validate the request parameters
            $request->validate([
                'status' => 'nullable|string|in:pending,processing,shipped,delivered,cancelled',
                'payment_status' => 'nullable|string|in:pending,paid,failed,refunded',
                'search' => 'nullable|string|max:255',
                'date_from' => 'nullable|date',
                'date_to' => 'nullable|date|after_or_equal:date_from',
            ]);

            // Check if there are any orders to export
            $ordersQuery = Order::query()->with(['user', 'items']);

            // Apply the same filters as in the export class
            if ($request->filled('status')) {
                $ordersQuery->where('status', $request->status);
            }

            if ($request->filled('payment_status')) {
                $ordersQuery->where('payment_status', $request->payment_status);
            }

            if ($request->filled('search')) {
                $search = $request->search;
                $ordersQuery->where(function ($q) use ($search) {
                    $q->where('order_number', 'like', "%{$search}%")
                      ->orWhereHas('user', function ($userQuery) use ($search) {
                          $userQuery->where('name', 'like', "%{$search}%")
                                   ->orWhere('email', 'like', "%{$search}%");
                      });
                });
            }

            if ($request->filled('date_from')) {
                $ordersQuery->whereDate('created_at', '>=', $request->date_from);
            }

            if ($request->filled('date_to')) {
                $ordersQuery->whereDate('created_at', '<=', $request->date_to);
            }

            $ordersCount = $ordersQuery->count();

            if ($ordersCount === 0) {
                return response()->json([
                    'error' => 'No orders found for the selected filters.'
                ], 404);
            }

            // Generate filename with timestamp
            $filename = 'orders-export-' . now()->format('Y-m-d-H-i-s') . '.xlsx';

            // Create the export
            $export = new OrdersExport($request);

            // Return the download response with proper headers
            return Excel::download(
                $export,
                $filename,
                \Maatwebsite\Excel\Excel::XLSX,
                [
                    'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                    'Cache-Control' => 'max-age=0',
                    'Cache-Control' => 'max-age=1',
                    'Expires' => 'Mon, 26 Jul 1997 05:00:00 GMT',
                    'Last-Modified' => gmdate('D, d M Y H:i:s') . ' GMT',
                    'Cache-Control' => 'cache, must-revalidate',
                    'Pragma' => 'public',
                ]
            );

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Orders export validation failed: ' . json_encode($e->errors()));
            return response()->json([
                'error' => 'Invalid filter parameters.',
                'details' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Orders export failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'error' => 'Export failed. Please try again.',
                'message' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }
}
