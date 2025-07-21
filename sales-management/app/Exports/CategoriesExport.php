<?php

namespace App\Exports;

use App\Models\Category;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class CategoriesExport implements FromQuery, WithHeadings, WithMapping, WithColumnWidths, WithStyles
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    /**
     * Query to fetch categories data
     */
    public function query()
    {
        $query = Category::query()->with(['parent', 'children', 'products']);

        // Apply filters similar to BrandsExport
        if (!empty($this->filters['search'])) {
            $query->where('name', 'like', "%{$this->filters['search']}%");
        }

        // if (isset($this->filters['status']) && $this->filters['status'] !== '') {
        //     $query->where('is_active', $this->filters['status']);
        // }

        if (isset($this->filters['parent_only']) && $this->filters['parent_only']) {
            $query->whereNull('parent_id');
        }

        if (isset($this->filters['parent_id']) && $this->filters['parent_id'] !== '') {
            $query->where('parent_id', $this->filters['parent_id']);
        }

        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Excel column headings
     */
    public function headings(): array
    {
        return [
            'ID',
            'Category Name',
            'Slug',
            'Description',
            'Parent Category',
            'Full Name',
            'Image URL',
            'Sort Order',
            'Status',
            'Total Products',
            'Sub Categories',
            'Created Date',
            'Updated Date'
        ];
    }

    /**
     * Map data for each row
     */
    public function map($category): array
    {
        return [
            $category->id,
            $category->name,
            $category->slug,
            $category->description ?? 'N/A',
            $category->parent ? $category->parent->name : 'Root Category',
            $category->full_name,
            $category->image ?? 'No Image',
            $category->sort_order ?? 0,
            $category->is_active ? 'Active' : 'Inactive',
            $category->products->count(),
            $category->children->count(),
            $category->created_at->format('Y-m-d H:i:s'),
            $category->updated_at->format('Y-m-d H:i:s')
        ];
    }

    /**
     * Set column widths
     */
    public function columnWidths(): array
    {
        return [
            'A' => 8,   // ID
            'B' => 25,  // Category Name
            'C' => 20,  // Slug
            'D' => 35,  // Description
            'E' => 20,  // Parent Category
            'F' => 30,  // Full Name
            'G' => 30,  // Image URL
            'H' => 12,  // Sort Order
            'I' => 12,  // Status
            'J' => 15,  // Total Products
            'K' => 15,  // Sub Categories
            'L' => 20,  // Created Date
            'M' => 20,  // Updated Date
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
        $sheet->getStyle('A1:M1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF']
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '28A745'] // Green color for categories
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
            $sheet->getStyle("A2:M{$highestRow}")->applyFromArray([
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

            // Center align numeric columns
            $sheet->getStyle("A2:A{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // ID
            $sheet->getStyle("H2:H{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // Sort Order
            $sheet->getStyle("I2:I{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // Status
            $sheet->getStyle("J2:J{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // Total Products
            $sheet->getStyle("K2:K{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // Sub Categories
        }

        // Set row height for header
        $sheet->getRowDimension(1)->setRowHeight(25);
        
        // Auto-wrap text for description and full name columns
        $sheet->getStyle("D:D")->getAlignment()->setWrapText(true); // Description
        $sheet->getStyle("F:F")->getAlignment()->setWrapText(true); // Full Name
        
        return [];
    }
}