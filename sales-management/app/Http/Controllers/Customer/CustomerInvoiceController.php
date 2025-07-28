<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;

class CustomerInvoiceController extends Controller
{
    /**
     * Download PDF invoice for customer's order
     */
    public function downloadInvoice($id)
    {
        try {
            // Find the order and ensure it belongs to the authenticated user
            $order = Order::with([
                'user',
                'items.product',
                'items.product.category'
            ])->where('id', $id)
              ->where('user_id', Auth::id())
              ->firstOrFail();

            // Validate that the order can have an invoice
            if ($order->status === 'cancelled') {
                return redirect()->back()->with('error', 'Cannot generate invoice for cancelled orders');
            }

            // Only allow invoice download for paid or completed orders
            if (!in_array($order->payment_status, ['paid']) || !in_array($order->status, ['processing', 'shipped', 'delivered'])) {
                return redirect()->back()->with('error', 'Invoice is not available for this order yet');
            }

            // Get company information
            $companyInfo = $this->getCompanyInfo();

            // Calculate additional invoice details
            $invoiceData = $this->prepareInvoiceData($order);

            // Generate PDF using the invoice view
            $pdf = Pdf::loadView('invoices.template', [
                'order' => $order,
                'company' => $companyInfo,
                'invoice' => $invoiceData
            ]);

            // Set PDF options
            $pdf->setPaper('A4', 'portrait');
            $pdf->setOptions([
                'dpi' => 150,
                'defaultFont' => 'sans-serif',
                'isHtml5ParserEnabled' => true,
                'isPhpEnabled' => true
            ]);

            // Generate filename
            $filename = "Invoice-{$order->order_number}-" . now()->format('Y-m-d') . ".pdf";

            // Return PDF download response
            return $pdf->download($filename);

        } catch (\Exception $e) {
            Log::error('Customer invoice PDF generation failed: ' . $e->getMessage(), [
                'order_id' => $id,
                'user_id' => Auth::id(),
                'error' => $e->getTraceAsString()
            ]);

            return redirect()->back()->with('error', 'Failed to generate invoice PDF. Please try again or contact support.');
        }
    }

    /**
     * Preview PDF invoice for customer's order
     */
    public function previewInvoice($id)
    {
        try {
            // Find the order and ensure it belongs to the authenticated user
            $order = Order::with([
                'user',
                'items.product',
                'items.product.category'
            ])->where('id', $id)
              ->where('user_id', Auth::id())
              ->firstOrFail();

            // Validate that the order can have an invoice
            if ($order->status === 'cancelled') {
                return response()->json(['error' => 'Cannot preview invoice for cancelled orders'], 400);
            }

            // Only allow invoice preview for paid or completed orders
            if (!in_array($order->payment_status, ['paid']) || !in_array($order->status, ['processing', 'shipped', 'delivered'])) {
                return response()->json(['error' => 'Invoice is not available for this order yet'], 400);
            }

            // Get company information
            $companyInfo = $this->getCompanyInfo();

            // Calculate additional invoice details
            $invoiceData = $this->prepareInvoiceData($order);

            // Generate PDF
            $pdf = Pdf::loadView('invoices.template', [
                'order' => $order,
                'company' => $companyInfo,
                'invoice' => $invoiceData
            ]);

            $pdf->setPaper('A4', 'portrait');

            // Stream PDF to browser for preview
            return $pdf->stream("Invoice-{$order->order_number}.pdf");

        } catch (\Exception $e) {
            Log::error('Customer invoice PDF preview failed: ' . $e->getMessage(), [
                'order_id' => $id,
                'user_id' => Auth::id()
            ]);

            return response()->json(['error' => 'Failed to preview invoice'], 500);
        }
    }

    /**
     * Get company information for invoice
     */
    private function getCompanyInfo()
    {
        return config('invoice.company', [
            'name' => config('app.name', 'Your Company Name'),
            'address' => '123 Business Street',
            'city' => 'Business City',
            'state' => 'State',
            'zip' => '12345',
            'country' => 'Country',
            'phone' => '+1 (555) 123-4567',
            'email' => 'info@company.com',
            'website' => 'www.company.com',
            'tax_id' => null,
            'logo' => null,
        ]);
    }

    /**
     * Prepare additional invoice data
     */
    private function prepareInvoiceData($order)
    {
        // Calculate totals
        $subtotal = $order->items->sum(function ($item) {
            return $item->quantity * $item->price;
        });

        // Get settings from config
        $settings = config('invoice.settings', []);
        $taxRate = $settings['default_tax_rate'] ?? 0.00;
        $currency = $settings['currency'] ?? 'USD';
        $currencySymbol = $settings['currency_symbol'] ?? '$';
        $invoicePrefix = $settings['invoice_prefix'] ?? 'INV-';
        $paymentDueDays = $settings['payment_due_days'] ?? 30;

        // Calculate tax
        $taxAmount = $subtotal * $taxRate;

        // Calculate shipping
        $shippingCost = $order->shipping_cost ?? 0;

        // Calculate discount
        $discountAmount = $order->discount_amount ?? 0;

        return [
            'invoice_number' => $invoicePrefix . $order->order_number,
            'invoice_date' => $order->created_at->format('Y-m-d'),
            'due_date' => $order->created_at->addDays($paymentDueDays)->format('Y-m-d'),
            'subtotal' => number_format($subtotal, 2),
            'tax_rate' => $taxRate * 100,
            'tax_amount' => number_format($taxAmount, 2),
            'shipping_cost' => number_format($shippingCost, 2),
            'discount_amount' => number_format($discountAmount, 2),
            'total_amount' => number_format($order->total_amount, 2),
            'currency' => $currency,
            'currency_symbol' => $currencySymbol,
        ];
    }
}
