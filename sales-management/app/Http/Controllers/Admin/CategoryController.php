<?php

namespace App\Http\Controllers\Admin;

use App\Exports\CategoriesExport;
use App\Imports\CategoriesImport;
use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $categories = Category::query()->with('parent')
        ->when($request->search, function ($query, $search){
            $query->where('name', 'like', "%{$search}%");
        })
        ->when($request->status !== null, function($query) use ($request){
            $query->where('is_active', $request->status);
        })
        ->orderBy('sort_order')
        ->orderBy('name')
        ->paginate(10)
        ->withQueryString();

        return Inertia::render('Admin/Categories/Index', [
            'categories' => $categories,
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
           $parentCategories = Category::whereNull('parent_id')
            ->active()
            ->orderBy('name')
            ->get();

        return Inertia::render('Admin/Categories/Create', [
            'parentCategories' => $parentCategories,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'parent_id' => 'nullable|exists:categories,id',
            'sort_order' => 'integer|min:0',
            'is_active' => 'boolean',
        ]);

        $category = new Category($validated);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('categories', 'public');
            $category->image = $path;
            Log::info('Logo uploaded', ['path' => $path]);
        }

        $category->save();

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category)
    {
       $category->load([
            'parent',
            'children',
            'products' => function ($query) {
                $query->with('brand')->take(10);
            }
        ]);

        return Inertia::render('Admin/Categories/Show', [
            'category' => $category,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Category $category)
    {
        $parentCategories = Category::whereNull('parent_id')
            ->where('id', '!=', $category->id)
            ->active()
            ->orderBy('name')
            ->get();

        return Inertia::render('Admin/Categories/Edit', [
            'category' => $category,
            'parentCategories' => $parentCategories,
        ]);
    }
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'parent_id' => 'nullable|exists:categories,id',
            'sort_order' => 'integer|min:0',
            'is_active' => 'boolean',
        ]);

       if ($request->hasFile('image')) {
            // Delete old logo if exists
            if ($category->image && Storage::disk('public')->exists($category->image)) {
                Storage::disk('public')->delete($category->image);
            }
            $path = $request->file('image')->store('categories', 'public');
            $validated['image'] = $path;
            Log::info('Image updated', ['path' => $path]);
        }


        $category->update($validated);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category)
    {
         if ($category->products()->count() > 0 || $category->children()->count() > 0) {
            return back()->with('error', 'Cannot delete category with associated products or subcategories.');
        }
        
        if ($category->image && Storage::disk('public')->exists($category->image)) {
            Storage::disk('public')->delete($category->image);
        }

        $category->delete();

       return redirect()->route('admin.categories.index')
            ->with('success', 'Category deleted successfully.');
    }

    public function toggleStatus(Category $category)
    {
        $category->update(['is_active' => !$category->is_active]);
        return redirect()->route('admin.categories.index')->with('success', 'Category status updated successfully.');
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
                'parent_only' => $request->get('parent_only'),
                'parent_id' => $request->get('parent_id'),
            ];

            // Generate filename with timestamp
            $timestamp = now()->format('Y-m-d_H-i-s');
            $filename = "categories_export_{$timestamp}.xlsx";

            // Create and download Excel file
            return Excel::download(new CategoriesExport($filters), $filename);

        } catch (\Exception $e) {
            Log::error('Category export failed: ' . $e->getMessage());
            
            return back()->with('error', 'Failed to export categories. Please try again.');
        }
    }

    /**
     * Import categories from Excel file
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
            $import = new CategoriesImport();

            Excel::import($import, $request->file('import_file'));

            $results = $import->getResults();

            // Prepare flash messages based on results
            $messages = [];

            if ($results['success_count'] > 0) {
                $messages[] = "Successfully imported {$results['success_count']} new categories.";
            }

            if ($results['update_count'] > 0) {
                $messages[] = "Updated {$results['update_count']} existing categories.";
            }

            if ($results['error_count'] > 0) {
                $messages[] = "Failed to process {$results['error_count']} rows due to errors.";
            }

            // If there are errors, store them in session for detailed view
            if (!empty($results['errors'])) {
                session(['import_errors' => $results['errors']]);

                return redirect()->route('admin.categories.index')
                    ->with('warning', implode(' ', $messages))
                    ->with('import_results', $results);
            }

            return redirect()->route('admin.categories.index')
                ->with('success', implode(' ', $messages))
                ->with('import_results', $results);

        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $failures = $e->failures();
            $errorMessages = [];

            foreach ($failures as $failure) {
                $errorMessages[] = "Row {$failure->row()}: " . implode(', ', $failure->errors());
            }

            return redirect()->route('admin.categories.index')
                ->with('error', 'Import failed due to validation errors: ' . implode(' | ', array_slice($errorMessages, 0, 5)) . (count($errorMessages) > 5 ? '... and ' . (count($errorMessages) - 5) . ' more errors.' : ''));

        } catch (\Exception $e) {
            Log::error('Category import failed', [
                'error' => $e->getMessage(),
                'file' => $request->file('import_file')?->getClientOriginalName()
            ]);

            return redirect()->route('admin.categories.index')
                ->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    /**
     * Download Excel template for category import
     */
    public function downloadTemplate()
    {
        try {
            $headers = CategoriesImport::getExpectedHeaders();
            $sampleData = CategoriesImport::getSampleData();

            // Create a simple CSV template
            $filename = 'categories_import_template_' . date('Y-m-d') . '.csv';
            $temp_file = tempnam(sys_get_temp_dir(), 'categories_template');

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

            return redirect()->route('admin.categories.index')
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
