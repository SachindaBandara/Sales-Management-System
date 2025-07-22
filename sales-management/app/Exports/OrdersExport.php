<?php

namespace App\Exports;

use App\Models\Order;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class OrdersExport implements FromQuery, WithHeadings, WithMapping, WithColumnWidths, WithStyles
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    /**
     * Query to fetch orders data
     */
    public function query()
    {
        $query = Order::query()->with(['user', 'items']);

        // Apply filters similar to typical order listing
        if (!empty($this->filters['search'])) {
            $query->where(function ($q) {
                $searchTerm = $this->filters['search'];
                $q->where('order_number', 'like', "%{$searchTerm}%")
                  ->orWhereHas('user', function ($userQuery) use ($searchTerm) {
                      $userQuery->where('name', 'like', "%{$searchTerm}%")
                                ->orWhere('email', 'like', "%{$searchTerm}%");
                  });
            });
        }

        if (isset($this->filters['status']) && $this->filters['status'] !== '') {
            $query->where('status', $this->filters['status']);
        }

        if (isset($this->filters['payment_status']) && $this->filters['payment_status'] !== '') {
            $query->where('payment_status', $this->filters['payment_status']);
        }

        if (!empty($this->filters['date_from'])) {
            $query->whereDate('created_at', '>=', $this->filters['date_from']);
        }

        if (!empty($this->filters['date_to'])) {
            $query->whereDate('created_at', '<=', $this->filters['date_to']);
        }

        if (!empty($this->filters['user_id'])) {
            $query->where('user_id', $this->filters['user_id']);
        }

        return $query->orderBy('created_at', 'desc');
    }

    /**
     * Excel column headings
     */
    public function headings(): array
    {
        return [
            'Order ID',
            'Order Number',
            'Customer Name',
            'Customer Email',
            'Status',
            'Payment Status',
            'Payment Method',
            'Subtotal (LKR)',
            'Tax Amount (LKR)',
            'Shipping Amount (LKR)',
            'Discount Amount (LKR)',
            'Total Amount (LKR)',
            'Currency',
            'Items Count',
            'Billing Address',
            'Shipping Address',
            'Notes',
            'Order Date',
            'Shipped Date',
            'Delivered Date',
            'Last Updated'
        ];
    }

    /**
     * Map data for each row
     */
    public function map($order): array
    {
        return [
            $order->id,
            $order->order_number,
            $order->user->name ?? 'Guest Customer',
            $order->user->email ?? 'N/A',
            ucfirst($order->status),
            ucfirst($order->payment_status),
            $order->payment_method ? ucfirst($order->payment_method) : 'N/A',
            number_format($order->subtotal, 2),
            number_format($order->tax_amount, 2),
            number_format($order->shipping_amount, 2),
            number_format($order->discount_amount, 2),
            number_format($order->total_amount, 2),
            $order->currency,
            $order->items->sum('quantity'),
            $this->formatAddress($order->billing_address),
            $this->formatAddress($order->shipping_address),
            $order->notes ?? 'No notes',
            $order->created_at->format('Y-m-d H:i:s'),
            $order->shipped_at ? $order->shipped_at->format('Y-m-d H:i:s') : 'Not shipped',
            $order->delivered_at ? $order->delivered_at->format('Y-m-d H:i:s') : 'Not delivered',
            $order->updated_at->format('Y-m-d H:i:s')
        ];
    }

    /**
     * Format address array to readable string
     */
    private function formatAddress($address): string
    {
        if (!$address || !is_array($address)) {
            return 'N/A';
        }

        $addressParts = [];

        if (!empty($address['line_1'])) {
            $addressParts[] = $address['line_1'];
        }

        if (!empty($address['line_2'])) {
            $addressParts[] = $address['line_2'];
        }

        if (!empty($address['city'])) {
            $addressParts[] = $address['city'];
        }

        if (!empty($address['state'])) {
            $addressParts[] = $address['state'];
        }

        if (!empty($address['postal_code'])) {
            $addressParts[] = $address['postal_code'];
        }

        if (!empty($address['country'])) {
            $addressParts[] = $address['country'];
        }

        return !empty($addressParts) ? implode(', ', $addressParts) : 'N/A';
    }

    /**
     * Set column widths
     */
    public function columnWidths(): array
    {
        return [
            'A' => 10,  // Order ID
            'B' => 18,  // Order Number
            'C' => 20,  // Customer Name
            'D' => 25,  // Customer Email
            'E' => 12,  // Status
            'F' => 15,  // Payment Status
            'G' => 15,  // Payment Method
            'H' => 15,  // Subtotal
            'I' => 15,  // Tax Amount
            'J' => 18,  // Shipping Amount
            'K' => 18,  // Discount Amount
            'L' => 18,  // Total Amount
            'M' => 10,  // Currency
            'N' => 12,  // Items Count
            'O' => 30,  // Billing Address
            'P' => 30,  // Shipping Address
            'Q' => 25,  // Notes
            'R' => 20,  // Order Date
            'S' => 20,  // Shipped Date
            'T' => 20,  // Delivered Date
            'U' => 20,  // Last Updated
        ];
    }

    /**
     * Apply styles to the spreadsheet
     */
    public function styles(Worksheet $sheet)
    {
        // Get the highest row and column
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();

        // Style the header row
        $sheet->getStyle("A1:{$highestColumn}1")->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF']
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '2E8B57'] // Sea Green color for orders
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000']
                ]
            ]
        ]);

        // Style all data rows
        if ($highestRow > 1) {
            $sheet->getStyle("A2:{$highestColumn}{$highestRow}")->applyFromArray([
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'CCCCCC']
                    ]
                ]
            ]);
        }

        // Right align currency columns
        $currencyColumns = ['H', 'I', 'J', 'K', 'L']; // Subtotal to Total Amount
        foreach ($currencyColumns as $column) {
            if ($highestRow > 1) {
                $sheet->getStyle("{$column}2:{$column}{$highestRow}")
                      ->getAlignment()
                      ->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            }
        }

        // Center align numeric columns
        $numericColumns = ['A', 'N']; // Order ID, Items Count
        foreach ($numericColumns as $column) {
            if ($highestRow > 1) {
                $sheet->getStyle("{$column}2:{$column}{$highestRow}")
                      ->getAlignment()
                      ->setHorizontal(Alignment::HORIZONTAL_CENTER);
            }
        }

        // Set row height for header
        $sheet->getRowDimension(1)->setRowHeight(30);

        // Auto-wrap text for address and notes columns
        $wrapColumns = ['O', 'P', 'Q']; // Billing Address, Shipping Address, Notes
        foreach ($wrapColumns as $column) {
            $sheet->getStyle("{$column}:{$column}")
                  ->getAlignment()
                  ->setWrapText(true);
        }

        // Apply alternate row coloring for better readability
        for ($row = 2; $row <= $highestRow; $row++) {
            if ($row % 2 == 0) {
                $sheet->getStyle("A{$row}:{$highestColumn}{$row}")->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'F8F9FA'] // Light gray
                    ]
                ]);
            }
        }

        return [];
    }
}
