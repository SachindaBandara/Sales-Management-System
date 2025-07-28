<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice {{ $invoice_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }

        /* Header */
        .header {
            display: table;
            width: 100%;
            margin-bottom: 30px;
            border-bottom: 2px solid #007bff;
            padding-bottom: 20px;
        }

        .header-left {
            display: table-cell;
            width: 60%;
            vertical-align: top;
        }

        .header-right {
            display: table-cell;
            width: 40%;
            vertical-align: top;
            text-align: right;
        }

        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: #007bff;
            margin-bottom: 5px;
        }

        .company-details {
            font-size: 11px;
            color: #666;
            line-height: 1.6;
        }

        .invoice-title {
            font-size: 28px;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
        }

        .invoice-details {
            font-size: 11px;
            color: #666;
        }

        /* Billing section */
        .billing-section {
            display: table;
            width: 100%;
            margin-bottom: 30px;
        }

        .bill-to, .ship-to {
            display: table-cell;
            width: 48%;
            vertical-align: top;
        }

        .bill-to {
            margin-right: 4%;
        }

        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: #007bff;
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 1px solid #eee;
        }

        .address {
            font-size: 11px;
            line-height: 1.6;
            color: #333;
        }

        /* Order details */
        .order-details {
            background: #f8f9fa;
            padding: 15px;
            margin-bottom: 30px;
            border-radius: 5px;
        }

        .order-details table {
            width: 100%;
            font-size: 11px;
        }

        .order-details td {
            padding: 3px 0;
        }

        .order-details .label {
            font-weight: bold;
            color: #666;
            width: 120px;
        }

        /* Items table */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
            font-size: 11px;
        }

        .items-table th {
            background: #007bff;
            color: white;
            padding: 12px 8px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #007bff;
        }

        .items-table td {
            padding: 10px 8px;
            border: 1px solid #ddd;
            vertical-align: top;
        }

        .items-table tbody tr:nth-child(even) {
            background: #f8f9fa;
        }

        .items-table .text-right {
            text-align: right;
        }

        .items-table .text-center {
            text-align: center;
        }

        /* Totals section */
        .totals-section {
            float: right;
            width: 300px;
            margin-top: 20px;
        }

        .totals-table {
            width: 100%;
            font-size: 12px;
        }

        .totals-table td {
            padding: 8px 12px;
            border-bottom: 1px solid #eee;
        }

        .totals-table .label {
            font-weight: bold;
            color: #666;
        }

        .totals-table .amount {
            text-align: right;
            font-weight: bold;
        }

        .totals-table .total-row {
            background: #007bff;
            color: white;
            font-size: 14px;
            font-weight: bold;
        }

        .totals-table .total-row td {
            border-bottom: none;
        }

        /* Status badges */
        .status-badge {
            padding: 4px 8px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .status-pending { background: #fff3cd; color: #856404; }
        .status-processing { background: #cce5ff; color: #004085; }
        .status-shipped { background: #e2d9f3; color: #6f42c1; }
        .status-delivered { background: #d4edda; color: #155724; }
        .status-cancelled { background: #f8d7da; color: #721c24; }
        .status-paid { background: #d4edda; color: #155724; }
        .status-failed { background: #f8d7da; color: #721c24; }
        .status-refunded { background: #ffeaa7; color: #856404; }

        /* Footer */
        .footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            font-size: 10px;
            color: #666;
            text-align: center;
        }

        /* Notes section */
        .notes-section {
            margin-top: 30px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 5px;
        }

        .notes-title {
            font-weight: bold;
            margin-bottom: 10px;
            color: #333;
        }

        /* Utility classes */
        .clearfix::after {
            content: "";
            display: table;
            clear: both;
        }

        .page-break {
            page-break-after: always;
        }

        /* Print styles */
        @media print {
            body { font-size: 11px; }
            .container { padding: 10px; }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="header-left">
                <div class="company-name">{{ $company['name'] }}</div>
                <div class="company-details">
                    {{ $company['address'] }}<br>
                    {{ $company['city'] }}, {{ $company['postal_code'] }}<br>
                    {{ $company['country'] }}<br>
                    Phone: {{ $company['phone'] }}<br>
                    Email: {{ $company['email'] }}<br>
                    @if($company['website'])
                        Website: {{ $company['website'] }}<br>
                    @endif
                    @if($company['tax_number'])
                        Tax ID: {{ $company['tax_number'] }}
                    @endif
                </div>
            </div>
            <div class="header-right">
                <div class="invoice-title">INVOICE</div>
                <div class="invoice-details">
                    <strong>Invoice #:</strong> {{ $invoice_number }}<br>
                    <strong>Order #:</strong> {{ $order->order_number }}<br>
                    <strong>Invoice Date:</strong> {{ $invoice_date }}<br>
                    <strong>Due Date:</strong> {{ $due_date }}
                </div>
            </div>
        </div>

        <!-- Billing Information -->
        <div class="billing-section">
            <div class="bill-to">
                <div class="section-title">Bill To:</div>
                <div class="address">
                    <strong>{{ $order->user->name }}</strong><br>
                    @if($order->user->email)
                        {{ $order->user->email }}<br>
                    @endif
                    @if($order->billing_address)
                        @php $billing = $order->billing_address; @endphp
                        @if(isset($billing['address']))
                            {{ $billing['address'] }}<br>
                        @endif
                        @if(isset($billing['city']) && isset($billing['postal_code']))
                            {{ $billing['city'] }}, {{ $billing['postal_code'] }}<br>
                        @endif
                        @if(isset($billing['country']))
                            {{ $billing['country'] }}<br>
                        @endif
                        @if(isset($billing['phone']))
                            Phone: {{ $billing['phone'] }}
                        @endif
                    @endif
                </div>
            </div>

            @if($order->shipping_address && $order->shipping_address !== $order->billing_address)
            <div class="ship-to">
                <div class="section-title">Ship To:</div>
                <div class="address">
                    @php $shipping = $order->shipping_address; @endphp
                    @if(isset($shipping['name']))
                        <strong>{{ $shipping['name'] }}</strong><br>
                    @endif
                    @if(isset($shipping['address']))
                        {{ $shipping['address'] }}<br>
                    @endif
                    @if(isset($shipping['city']) && isset($shipping['postal_code']))
                        {{ $shipping['city'] }}, {{ $shipping['postal_code'] }}<br>
                    @endif
                    @if(isset($shipping['country']))
                        {{ $shipping['country'] }}<br>
                    @endif
                    @if(isset($shipping['phone']))
                        Phone: {{ $shipping['phone'] }}
                    @endif
                </div>
            </div>
            @endif
        </div>

        <!-- Order Details -->
        <div class="order-details">
            <table>
                <tr>
                    <td class="label">Order Status:</td>
                    <td><span class="status-badge status-{{ $order->status }}">{{ $order->status_badge['text'] }}</span></td>
                    <td class="label" style="padding-left: 30px;">Payment Status:</td>
                    <td><span class="status-badge status-{{ $order->payment_status }}">{{ $order->payment_status_badge['text'] }}</span></td>
                </tr>
                <tr>
                    <td class="label">Payment Method:</td>
                    <td>{{ ucfirst($order->payment_method ?? 'N/A') }}</td>
                    <td class="label" style="padding-left: 30px;">Currency:</td>
                    <td>{{ $order->currency }}</td>
                </tr>
                @if($order->shipped_at)
                <tr>
                    <td class="label">Shipped Date:</td>
                    <td>{{ $order->shipped_at->format('Y-m-d H:i') }}</td>
                    @if($order->delivered_at)
                    <td class="label" style="padding-left: 30px;">Delivered Date:</td>
                    <td>{{ $order->delivered_at->format('Y-m-d H:i') }}</td>
                    @endif
                </tr>
                @endif
            </table>
        </div>

        <!-- Items Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 50px;">#</th>
                    <th>Product</th>
                    <th style="width: 100px;">SKU</th>
                    <th style="width: 80px;" class="text-center">Qty</th>
                    <th style="width: 100px;" class="text-right">Unit Price</th>
                    <th style="width: 100px;" class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>
                        <strong>{{ $item->product_name }}</strong>
                        @if($item->product_snapshot && isset($item->product_snapshot['description']))
                            <br><small style="color: #666;">{{ Str::limit($item->product_snapshot['description'], 100) }}</small>
                        @endif
                    </td>
                    <td>{{ $item->product_sku ?? 'N/A' }}</td>
                    <td class="text-center">{{ $item->quantity }}</td>
                    <td class="text-right">{{ $item->formatted_price }}</td>
                    <td class="text-right">{{ $item->formatted_total }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Totals -->
        <div class="totals-section">
            <table class="totals-table">
                <tr>
                    <td class="label">Subtotal:</td>
                    <td class="amount">LKR{{ number_format($order->subtotal, 2) }}</td>
                </tr>
                @if($order->discount_amount > 0)
                <tr>
                    <td class="label">Discount:</td>
                    <td class="amount">-LKR{{ number_format($order->discount_amount, 2) }}</td>
                </tr>
                @endif
                @if($order->tax_amount > 0)
                <tr>
                    <td class="label">Tax:</td>
                    <td class="amount">LKR{{ number_format($order->tax_amount, 2) }}</td>
                </tr>
                @endif
                @if($order->shipping_amount > 0)
                <tr>
                    <td class="label">Shipping:</td>
                    <td class="amount">LKR{{ number_format($order->shipping_amount, 2) }}</td>
                </tr>
                @endif
                <tr class="total-row">
                    <td>TOTAL:</td>
                    <td class="amount">{{ $order->formatted_total }}</td>
                </tr>
            </table>
        </div>

        <div class="clearfix"></div>

        <!-- Notes -->
        @if($order->notes)
        <div class="notes-section">
            <div class="notes-title">Notes:</div>
            <div>{{ $order->notes }}</div>
        </div>
        @endif

        <!-- Footer -->
        <div class="footer">
            <p>Thank you for your business!</p>
            <p>This invoice was generated on {{ now()->format('Y-m-d H:i:s') }}</p>
            @if($company['email'])
                <p>For any questions regarding this invoice, please contact us at {{ $company['email'] }}</p>
            @endif
        </div>
    </div>
</body>
</html>
