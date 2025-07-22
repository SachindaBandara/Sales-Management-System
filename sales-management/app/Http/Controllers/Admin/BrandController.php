<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use App\Exports\BrandsExport;
use App\Imports\BrandsImport;
use Maatwebsite\Excel\Facades\Excel;

class BrandController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $brands = Brand::query()
            // Filter by search term
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%");
            })
            // Filter by status
            ->when($request->status !== null, function ($query) use ($request) {
                $query->where('is_active', $request->status);
            })
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Admin/Brands/Index', [
            'brands' => $brands,
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Admin/Brands/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|unique:brands,slug|max:255',
            'description' => 'nullable|string',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_active' => 'boolean',
        ]);

        $brand = new Brand($validated);

        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('brand_logos', 'public');
            $brand->logo = $path;
            Log::info('Logo uploaded', ['path' => $path]);
        }

        $brand->save();

        return redirect()->route('admin.brands.index')->with('success', 'Brand created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Brand $brand)
    {
        $brand->load(['products' => function ($query) {
            $query->with('category')->take(10);
        }]);

       return Inertia::render('Admin/Brands/Show', [
            'brand' => $brand,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Brand $brand)
    {

        return Inertia::render('Admin/Brands/Edit', [
            'brand' => $brand,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Brand $brand)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|unique:brands,slug,' . $brand->id . '|max:255',
            'description' => 'nullable|string',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_active' => 'boolean',
        ]);

       if ($request->hasFile('logo')) {
            // Delete old logo if exists
            if ($brand->logo && Storage::disk('public')->exists($brand->logo)) {
                Storage::disk('public')->delete($brand->logo);
            }
            $path = $request->file('logo')->store('brand_logos', 'public');
            $validated['logo'] = $path;
            Log::info('Logo updated', ['path' => $path]);
        }


        $brand->update($validated);

        return redirect()->route('admin.brands.index')
            ->with('success', 'Brand updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Brand $brand)
    {
        if ($brand->products()->count() > 0) {
            return back()->with('error', 'Cannot delete brand with associated products.');
        }

        if ($brand->logo && Storage::disk('public')->exists($brand->logo)) {
            Storage::disk('public')->delete($brand->logo);
        }

        $brand->delete();

        return redirect()->route('admin.brands.index')
            ->with('success', 'Brand deleted successfully.');
    }

    public function toggleStatus(Brand $brand)
    {
        $brand->update(['is_active' => !$brand->is_active]);
        return redirect()->route('admin.brands.index')->with('success', 'Brand status updated successfully.');
    }

    /**
     * Export brands to Excel
     */
    public function export(Request $request)
    {
        try {
            // Get filters from request
            $filters = [
                'search' => $request->get('search'),
            ];

            // Generate filename with timestamp
            $timestamp = now()->format('Y-m-d_H-i-s');
            $filename = "brands_export_{$timestamp}.xlsx";

            // Create and download Excel file
            return Excel::download(new BrandsExport($filters), $filename);

        } catch (\Exception $e) {
            Log::error('Brand export failed: ' . $e->getMessage());

            return back()->with('error', 'Failed to export brands. Please try again.');
        }
    }

     /**
     * Import brands from Excel file
     */
    public function import(Request $request)
    {
        $request->validate([
            'import_file' => [
                'required',
                'file',
                'mimes:xlsx,xls,csv',
                'max:10240' // 10MB max file size
            ]
        ], [
            'import_file.required' => 'Please select a file to import.',
            'import_file.mimes' => 'The file must be an Excel file (.xlsx, .xls) or CSV file (.csv).',
            'import_file.max' => 'The file size must not exceed 10MB.'
        ]);

        try {
            $import = new BrandsImport();

            Excel::import($import, $request->file('import_file'));

            $results = $import->getResults();

            // Prepare flash messages based on results
            $messages = [];

            if ($results['success_count'] > 0) {
                $messages[] = "Successfully imported {$results['success_count']} new brands.";
            }

            if ($results['update_count'] > 0) {
                $messages[] = "Updated {$results['update_count']} existing brands.";
            }

            if ($results['error_count'] > 0) {
                $messages[] = "Failed to process {$results['error_count']} rows due to errors.";
            }

            // If there are errors, store them in session for detailed view
            if (!empty($results['errors'])) {
                session(['import_errors' => $results['errors']]);

                return redirect()->route('admin.brands.index')
                    ->with('warning', implode(' ', $messages))
                    ->with('import_results', $results);
            }

            return redirect()->route('admin.brands.index')
                ->with('success', implode(' ', $messages))
                ->with('import_results', $results);

        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $failures = $e->failures();
            $errorMessages = [];

            foreach ($failures as $failure) {
                $errorMessages[] = "Row {$failure->row()}: " . implode(', ', $failure->errors());
            }

            return redirect()->route('admin.brands.index')
                ->with('error', 'Import failed due to validation errors: ' . implode(' | ', array_slice($errorMessages, 0, 5)) . (count($errorMessages) > 5 ? '... and ' . (count($errorMessages) - 5) . ' more errors.' : ''));

        } catch (\Exception $e) {
            Log::error('Brand import failed', [
                'error' => $e->getMessage(),
                'file' => $request->file('import_file')?->getClientOriginalName()
            ]);

            return redirect()->route('admin.brands.index')
                ->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    /**
     * Download Excel template for brand import
     */
    public function downloadTemplate()
    {
        try {
            $headers = BrandsImport::getExpectedHeaders();
            $sampleData = BrandsImport::getSampleData();

            // Create a simple CSV template
            $filename = 'brands_import_template_' . date('Y-m-d') . '.csv';
            $temp_file = tempnam(sys_get_temp_dir(), 'brands_template');

            $handle = fopen($temp_file, 'w');

            // Write headers
            fputcsv($handle, array_keys($headers));

            // Write header descriptions as a comment row
            fputcsv($handle, array_values($headers));

            // Write sample data
            foreach ($sampleData as $row) {
                fputcsv($handle, $row);
            }

            fclose($handle);

            return response()->download($temp_file, $filename, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"'
            ])->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            Log::error('Failed to generate template', ['error' => $e->getMessage()]);

            return redirect()->route('admin.brands.index')
                ->with('error', 'Failed to generate template file.');
        }
    }

    /**
     * Get import errors for display
     */
    public function getImportErrors()
    {
        $errors = session('import_errors', []);
        session()->forget('import_errors');

        return response()->json($errors);
    }
}
