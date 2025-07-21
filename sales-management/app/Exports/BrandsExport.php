<?php

namespace App\Exports;

use App\Models\Brand;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class BrandsExport implements FromQuery, WithHeadings, WithMapping, WithColumnWidths, WithStyles
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    /**
     * Query to fetch brands data
     */
    public function query()
    {
        $query = Brand::query()->with('products');

        // Apply the same filters as in the index method
        if (!empty($this->filters['search'])) {
            $query->where('name', 'like', "%{$this->filters['search']}%");
        }

        if (isset($this->filters['status']) && $this->filters['status'] !== '') {
            $query->where('is_active', $this->filters['status']);
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
            'Brand Name',
            'Slug',
            'Description',
            'Logo URL',
            'Status',
            'Total Products',
            'Created Date',
            'Updated Date'
        ];
    }

    /**
     * Map data for each row
     */
    public function map($brand): array
    {
        return [
            $brand->id,
            $brand->name,
            $brand->slug,
            $brand->description ?? 'N/A',
            $brand->logo ?? 'No Logo',
            $brand->is_active ? 'Active' : 'Inactive',
            $brand->products->count(),
            $brand->created_at->format('Y-m-d H:i:s'),
            $brand->updated_at->format('Y-m-d H:i:s')
        ];
    }

    /**
     * Set column widths
     */
    public function columnWidths(): array
    {
        return [
            'A' => 8,   // ID
            'B' => 25,  // Brand Name
            'C' => 20,  // Slug
            'D' => 35,  // Description
            'E' => 30,  // Logo URL
            'F' => 12,  // Status
            'G' => 15,  // Total Products
            'H' => 20,  // Created Date
            'I' => 20,  // Updated Date
        ];
    }

    /**
     * Apply styles to the spreadsheet
     */
    public function styles(Worksheet $sheet)
    {
        // Get the highest row number
        $highestRow = $sheet->getHighestRow();
        
        // Style the header row
        $sheet->getStyle('A1:I1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF']
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4A90E2']
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
            $sheet->getStyle("A2:I{$highestRow}")->applyFromArray([
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

        // Set row height for header
        $sheet->getRowDimension(1)->setRowHeight(25);
        
        // Auto-wrap text for description column
        $sheet->getStyle("D:D")->getAlignment()->setWrapText(true);
        
        return [];
    }
}
