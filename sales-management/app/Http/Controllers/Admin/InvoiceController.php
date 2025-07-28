<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class InvoiceController extends Controller
{
    /**
     * Display the invoice page for an order
     */
    public function show(Order $order)
    {
        try {
            // Load order with relationships
            $order->load(['user', 'items.product']);

            // Log the invoice view
            Log::info('Invoice viewed', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'admin_id' => Auth::id()
            ]);

            return Inertia::render('Admin/Invoice/Show', [
                'order' => $order
            ]);

        } catch (\Exception $e) {
            Log::error('Error displaying invoice', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
                'admin_id' => Auth::id()
            ]);

            return redirect()->back()->with('error', 'Unable to display invoice');
        }
    }

    /**
     * Download the invoice as PDF
     */
    public function download(Order $order)
    {
        try {
            // Load order with relationships
            $order->load(['user', 'items.product']);

            // Validate order has required data
            if (!$order->user) {
                throw new \Exception('Order user not found');
            }

            if ($order->items->isEmpty()) {
                throw new \Exception('Order has no items');
            }

            // Generate PDF
            $pdf = $this->generateInvoicePdf($order);

            // Log the download
            Log::info('Invoice PDF downloaded', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'admin_id' => Auth::id()
            ]);

            // Generate filename
            $filename = 'invoice-' . $order->order_number . '.pdf';

            return $pdf->download($filename);

        } catch (\Exception $e) {
            Log::error('Error generating invoice PDF', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
                'admin_id' => Auth::id()
            ]);

            return redirect()->back()->with('error', 'Unable to generate invoice PDF: ' . $e->getMessage());
        }
    }

    /**
     * Preview the invoice PDF in browser
     */
    public function preview(Order $order)
    {
        try {
            // Load order with relationships
            $order->load(['user', 'items.product']);

            // Generate PDF
            $pdf = $this->generateInvoicePdf($order);

            // Log the preview
            Log::info('Invoice PDF previewed', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'admin_id' => Auth::id()
            ]);

            return $pdf->stream('invoice-' . $order->order_number . '.pdf');

        } catch (\Exception $e) {
            Log::error('Error previewing invoice PDF', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
                'admin_id' => Auth::id()
            ]);

            return response()->json(['error' => 'Unable to preview invoice PDF'], 500);
        }
    }

    /**
     * Generate the invoice PDF
     */
    private function generateInvoicePdf(Order $order)
    {
        // Prepare data for the invoice
        $invoiceData = [
            'order' => $order,
            'company' => [
                'name' => config('app.name', 'Your Company'),
                'address' => config('invoice.company.address', '123 Business Street'),
                'city' => config('invoice.company.city', 'Kalutara'),
                'postal_code' => config('invoice.company.postal_code', '12000'),
                'country' => config('invoice.company.country', 'Sri Lanka'),
                'phone' => config('invoice.company.phone', '+94 XX XXX XXXX'),
                'email' => config('invoice.company.email', 'info@company.com'),
                'website' => config('invoice.company.website', 'www.company.com'),
                'tax_number' => config('invoice.company.tax_number', 'TAX123456'),
            ],
            'invoice_date' => now()->format('Y-m-d'),
            'due_date' => now()->addDays(30)->format('Y-m-d'),
            'invoice_number' => 'INV-' . $order->order_number,
        ];

        // Generate PDF with custom options
        $pdf = Pdf::loadView('invoices.template', $invoiceData)
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'dpi' => 150,
                'defaultFont' => 'sans-serif',
                'isHtml5ParserEnabled' => true,
                'isPhpEnabled' => true,
                'isRemoteEnabled' => true,
            ]);

        return $pdf;
    }

    /**
     * Bulk download invoices for multiple orders
     */
    public function bulkDownload(Request $request)
    {
        try {
            $request->validate([
                'order_ids' => 'required|array|min:1',
                'order_ids.*' => 'exists:orders,id'
            ]);

            $orders = Order::with(['user', 'items.product'])
                ->whereIn('id', $request->order_ids)
                ->get();

            if ($orders->isEmpty()) {
                return redirect()->back()->with('error', 'No valid orders found');
            }

            // Create a ZIP file containing all invoices
            $zip = new \ZipArchive();
            $zipFileName = 'invoices-' . now()->format('Y-m-d-H-i-s') . '.zip';
            $zipPath = storage_path('app/temp/' . $zipFileName);

            // Ensure temp directory exists
            if (!file_exists(dirname($zipPath))) {
                mkdir(dirname($zipPath), 0755, true);
            }

            if ($zip->open($zipPath, \ZipArchive::CREATE) === TRUE) {
                foreach ($orders as $order) {
                    $pdf = $this->generateInvoicePdf($order);
                    $pdfContent = $pdf->output();
                    $zip->addFromString('invoice-' . $order->order_number . '.pdf', $pdfContent);
                }
                $zip->close();

                Log::info('Bulk invoice download', [
                    'order_count' => $orders->count(),
                    'admin_id' => Auth::id()
                ]);

                return response()->download($zipPath, $zipFileName)->deleteFileAfterSend(true);
            }

            throw new \Exception('Unable to create ZIP file');

        } catch (\Exception $e) {
            Log::error('Error in bulk invoice download', [
                'error' => $e->getMessage(),
                'admin_id' => Auth::id()
            ]);

            return redirect()->back()->with('error', 'Unable to generate bulk invoices: ' . $e->getMessage());
        }
    }
}
