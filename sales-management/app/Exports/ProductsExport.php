<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ProductsExport implements FromQuery, WithHeadings, WithMapping, WithColumnWidths, WithStyles
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    /**
     * Query to fetch products data
     */
    public function query()
    {
        $query = Product::query()->with(['brand', 'category']);

        // Apply filters similar to typical product filtering
        if (!empty($this->filters['search'])) {
            $query->where(function ($q) {
                $searchTerm = $this->filters['search'];
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('sku', 'like', "%{$searchTerm}%")
                  ->orWhere('description', 'like', "%{$searchTerm}%");
            });
        }

        if (isset($this->filters['status']) && $this->filters['status'] !== '') {
            $query->where('status', $this->filters['status']);
        }

        if (isset($this->filters['brand_id']) && $this->filters['brand_id'] !== '') {
            $query->where('brand_id', $this->filters['brand_id']);
        }

        if (isset($this->filters['category_id']) && $this->filters['category_id'] !== '') {
            $query->where('category_id', $this->filters['category_id']);
        }

        if (isset($this->filters['stock_status'])) {
            switch ($this->filters['stock_status']) {
                case 'in_stock':
                    $query->inStock();
                    break;
                case 'out_of_stock':
                    $query->where('track_quantity', true)->where('stock_quantity', '<=', 0);
                    break;
                case 'low_stock':
                    $query->where('track_quantity', true)->where('stock_quantity', '>', 0)->where('stock_quantity', '<=', 10);
                    break;
            }
        }

        if (isset($this->filters['price_min']) && $this->filters['price_min'] !== '') {
            $query->where('price', '>=', $this->filters['price_min']);
        }

        if (isset($this->filters['price_max']) && $this->filters['price_max'] !== '') {
            $query->where('price', '<=', $this->filters['price_max']);
        }

        return $query->orderBy('name');
    }

    /**
     * Excel column headings
     */
    public function headings(): array
    {
        return [
            'ID',
            'Product Name',
            'Slug',
            'SKU',
            'Description',
            'Short Description',
            'Price (LKR)',
            'Compare Price (LKR)',
            'Discount %',
            'Stock Quantity',
            'Track Quantity',
            'Stock Status',
            'Weight (kg)',
            'Dimensions',
            'Status',
            'Brand',
            'Category',
            'Images Count',
            'Primary Image',
            'Total Sold',
            'Revenue (LKR)',
            'Created Date',
            'Updated Date'
        ];
    }

    /**
     * Map data for each row
     */
    public function map($product): array
    {
        return [
            $product->id,
            $product->name,
            $product->slug,
            $product->sku ?? 'N/A',
            $this->truncateText($product->description ?? 'N/A', 100),
            $this->truncateText($product->short_description ?? 'N/A', 50),
            number_format($product->price, 2),
            $product->compare_price ? number_format($product->compare_price, 2) : 'N/A',
            $product->discount_percentage > 0 ? $product->discount_percentage . '%' : 'No Discount',
            $product->track_quantity ? $product->stock_quantity : 'Not Tracked',
            $product->track_quantity ? 'Yes' : 'No',
            $product->stock_status,
            $product->weight ? number_format($product->weight, 2) : 'N/A',
            $this->formatDimensions($product->dimensions),
            ucfirst($product->status),
            $product->brand ? $product->brand->name : 'No Brand',
            $product->category ? $product->category->name : 'No Category',
            $product->image_count,
            $product->hasImages() ? 'Yes' : 'No',
            $product->total_sold ?? 0,
            $product->revenue ? number_format($product->revenue, 2) : '0.00',
            $product->created_at->format('Y-m-d H:i:s'),
            $product->updated_at->format('Y-m-d H:i:s')
        ];
    }

    /**
     * Set column widths
     */
    public function columnWidths(): array
    {
        return [
            'A' => 8,   // ID
            'B' => 30,  // Product Name
            'C' => 25,  // Slug
            'D' => 15,  // SKU
            'E' => 40,  // Description
            'F' => 30,  // Short Description
            'G' => 15,  // Price
            'H' => 18,  // Compare Price
            'I' => 12,  // Discount %
            'J' => 15,  // Stock Quantity
            'K' => 15,  // Track Quantity
            'L' => 15,  // Stock Status
            'M' => 12,  // Weight
            'N' => 20,  // Dimensions
            'O' => 12,  // Status
            'P' => 20,  // Brand
            'Q' => 20,  // Category
            'R' => 15,  // Images Count
            'S' => 15,  // Primary Image
            'T' => 12,  // Total Sold
            'U' => 15,  // Revenue
            'V' => 20,  // Created Date
            'W' => 20,  // Updated Date
        ];
    }

    /**
     * Apply styles to the spreadsheet
     */
    public function styles(Worksheet $sheet)
    {
        // Get the highest row number
        $highestRow = $sheet->getHighestRow();
        $highestColumn = 'W'; // Last column
        
        // Style the header row
        $sheet->getStyle("A1:{$highestColumn}1")->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 11
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '2E7D32'] // Green header
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

            // Alternate row colors for better readability
            for ($row = 2; $row <= $highestRow; $row++) {
                if ($row % 2 == 0) {
                    $sheet->getStyle("A{$row}:{$highestColumn}{$row}")->applyFromArray([
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => 'F8F9FA']
                        ]
                    ]);
                }
            }

            // Highlight out of stock products in red
            for ($row = 2; $row <= $highestRow; $row++) {
                $stockStatus = $sheet->getCell("L{$row}")->getValue();
                if ($stockStatus === 'Out of Stock') {
                    $sheet->getStyle("A{$row}:{$highestColumn}{$row}")->applyFromArray([
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => 'FFEBEE']
                        ],
                        'font' => [
                            'color' => ['rgb' => 'C62828']
                        ]
                    ]);
                }
            }

            // Highlight low stock products in yellow
            for ($row = 2; $row <= $highestRow; $row++) {
                $stockStatus = $sheet->getCell("L{$row}")->getValue();
                if ($stockStatus === 'Low Stock') {
                    $sheet->getStyle("A{$row}:{$highestColumn}{$row}")->applyFromArray([
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => 'FFF8E1']
                        ],
                        'font' => [
                            'color' => ['rgb' => 'F57F17']
                        ]
                    ]);
                }
            }
        }

        // Set row height for header
        $sheet->getRowDimension(1)->setRowHeight(30);
        
        // Auto-wrap text for description columns
        $sheet->getStyle("E:F")->getAlignment()->setWrapText(true);
        
        // Center align specific columns
        $sheet->getStyle("A:A")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // ID
        $sheet->getStyle("G:I")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT); // Price columns
        $sheet->getStyle("J:J")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // Stock Quantity
        $sheet->getStyle("K:K")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // Track Quantity
        $sheet->getStyle("L:L")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // Stock Status
        $sheet->getStyle("O:O")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // Status
        $sheet->getStyle("R:S")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // Image columns
        $sheet->getStyle("T:U")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT); // Sales columns

        return [];
    }

    /**
     * Helper method to truncate text
     */
    private function truncateText($text, $length = 50)
    {
        if (strlen($text) <= $length) {
            return $text;
        }
        return substr($text, 0, $length) . '...';
    }

    /**
     * Helper method to format dimensions
     */
    private function formatDimensions($dimensions)
    {
        if (!$dimensions || !is_array($dimensions)) {
            return 'N/A';
        }

        $formatted = [];
        if (isset($dimensions['length'])) $formatted[] = 'L:' . $dimensions['length'];
        if (isset($dimensions['width'])) $formatted[] = 'W:' . $dimensions['width'];
        if (isset($dimensions['height'])) $formatted[] = 'H:' . $dimensions['height'];

        return !empty($formatted) ? implode(', ', $formatted) : 'N/A';
    }
}