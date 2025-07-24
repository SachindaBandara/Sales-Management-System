<?php

namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Models\User;
use App\Exports\UsersExport;
use App\Imports\UsersImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = User::query();

        // Search functionality
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        // Role filter
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $users = $query->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'filters' => $request->only(['search', 'role', 'status']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        return Inertia::render('Admin/Users/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:customer,admin',
            'is_active' => 'boolean',
        ]);

        User::create($validated);

        return redirect()->route('admin.users.index')
            ->with('success', 'User created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        return Inertia::render('Admin/Users/Show', [
            'user' => $user,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        return Inertia::render('Admin/Users/Edit', [
            'user' => $user,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'role' => 'required|in:customer,admin',
            'is_active' => 'boolean',
        ]);

        // Only validate password if it's being updated
        if ($request->filled('password')) {
            $request->validate([
                'password' => 'required|string|min:8|confirmed',
            ]);
            $validated['password'] = $request->password;
        }

        $user->update($validated);

        return redirect()->route('admin.users.index')
            ->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
         // Prevent admin from deleting themselves
        if ($user->id === Auth::user()->id) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        return back()->with('success', 'User deleted successfully.');
    }

    
    public function toggleStatus(User $user){
        // Prevent admin from deactivating themselves
        if ($user->id === Auth::user()->id) {
            return back()->with('error', 'You cannot deactivate your own account.');
        }

        $user->update(['is_active' => !$user->is_active]);

        $status = $user->is_active ? 'activated' : 'deactivated';
        return back()->with('success', "User {$status} successfully.");
    }

    /**
     * Export users to Excel
     */
    public function export(Request $request)
    {
        try {
            // Get filters from request
            $filters = [
                'search' => $request->get('search'),
                'role' => $request->get('role'),
            ];

            // Generate filename with timestamp
            $timestamp = now()->format('Y-m-d_H-i-s');
            $filename = "users_export_{$timestamp}.xlsx";

            // Create and download Excel file
            return Excel::download(new UsersExport($filters), $filename);

        } catch (\Exception $e) {
            Log::error('Users export failed: ' . $e->getMessage());

            return back()->with('error', 'Failed to export users. Please try again.');
        }
    }

    /**
 * Import users from Excel file
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
        $import = new UsersImport();

        Excel::import($import, $request->file('import_file'));

        $results = $import->getResults();

        // Prepare flash messages based on results
        $messages = [];

        if ($results['success_count'] > 0) {
            $messages[] = "Successfully imported {$results['success_count']} new users.";
        }

        if ($results['update_count'] > 0) {
            $messages[] = "Updated {$results['update_count']} existing users.";
        }

        if ($results['error_count'] > 0) {
            $messages[] = "Failed to process {$results['error_count']} rows due to errors.";
        }

        // If there are errors, store them in session for detailed view
        if (!empty($results['errors'])) {
            session(['user_import_errors' => $results['errors']]);

            return redirect()->route('admin.users.index')
                ->with('warning', implode(' ', $messages))
                ->with('import_results', $results);
        }

        return redirect()->route('admin.users.index')
            ->with('success', implode(' ', $messages))
            ->with('import_results', $results);

    } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
        $failures = $e->failures();
        $errorMessages = [];

        foreach ($failures as $failure) {
            $errorMessages[] = "Row {$failure->row()}: " . implode(', ', $failure->errors());
        }

        return redirect()->route('admin.users.index')
            ->with('error', 'Import failed due to validation errors: ' . implode(' | ', array_slice($errorMessages, 0, 5)) . (count($errorMessages) > 5 ? '... and ' . (count($errorMessages) - 5) . ' more errors.' : ''));

    } catch (\Exception $e) {
        Log::error('User import failed', [
            'error' => $e->getMessage(),
            'file' => $request->file('import_file')?->getClientOriginalName()
        ]);

        return redirect()->route('admin.users.index')
            ->with('error', 'Import failed: ' . $e->getMessage());
    }
}

/**
 * Download Excel template for user import
 */
public function downloadTemplate()
{
    try {
        $headers = UsersImport::getExpectedHeaders();
        $sampleData = UsersImport::getSampleData();

        // Create a simple CSV template
        $filename = 'users_import_template_' . date('Y-m-d') . '.csv';
        $temp_file = tempnam(sys_get_temp_dir(), 'users_template');

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
        Log::error('Failed to generate user template', ['error' => $e->getMessage()]);

        return redirect()->route('admin.users.index')
            ->with('error', 'Failed to generate template file.');
    }
}

/**
 * Get user import errors for display
 */
public function getImportErrors()
{
    $errors = session('user_import_errors', []);
    session()->forget('user_import_errors');

    return response()->json($errors);
}
}
