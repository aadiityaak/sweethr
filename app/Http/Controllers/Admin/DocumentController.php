<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDocumentRequest;
use App\Models\DocumentType;
use App\Models\EmployeeDocument;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class DocumentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = EmployeeDocument::query()
            ->with(['user:id,name,employee_id', 'documentType:id,name', 'uploadedBy:id,name'])
            ->latest();

        // Filter by employee
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by document type
        if ($request->filled('document_type_id')) {
            $query->where('document_type_id', $request->document_type_id);
        }


        // Filter expiring soon
        if ($request->boolean('expiring_soon')) {
            $query->expiringSoon();
        }

        // Filter expired
        if ($request->boolean('expired')) {
            $query->expired();
        }

        $documents = $query->paginate(15)->withQueryString();

        // Get filter options
        $users = User::select('id', 'name', 'employee_id')->orderBy('name')->get();
        $documentTypes = DocumentType::select('id', 'name')->active()->orderBy('name')->get();

        // Get summary stats
        $stats = [
            'total' => EmployeeDocument::count(),
            'active' => EmployeeDocument::where('is_active', true)->count(),
            'expiring_soon' => EmployeeDocument::expiringSoon()->count(),
            'expired' => EmployeeDocument::expired()->count(),
        ];

        return Inertia::render('admin/Documents/Index', [
            'documents' => $documents,
            'users' => $users,
            'documentTypes' => $documentTypes,
            'stats' => $stats,
            'filters' => $request->only(['user_id', 'document_type_id', 'expiring_soon', 'expired']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $users = User::select('id', 'name', 'employee_id')->orderBy('name')->get();
        $documentTypes = DocumentType::select('id', 'name', 'code', 'requires_expiry', 'allowed_extensions', 'max_file_size_mb')
            ->active()
            ->orderBy('name')
            ->get();

        return Inertia::render('admin/Documents/Create', [
            'users' => $users,
            'documentTypes' => $documentTypes,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDocumentRequest $request)
    {
        $documentType = DocumentType::findOrFail($request->document_type_id);
        $file = $request->file('file');

        // Generate unique filename
        $filename = time().'_'.$file->getClientOriginalName();
        $path = $file->storeAs('documents', $filename, 'private');

        $document = EmployeeDocument::create([
            'user_id' => $request->user_id,
            'document_type_id' => $request->document_type_id,
            'title' => $request->title,
            'description' => $request->description,
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'file_extension' => $file->getClientOriginalExtension(),
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'issued_date' => $request->issued_date,
            'expiry_date' => $request->expiry_date ?: ($documentType->requires_expiry && $documentType->default_validity_months ?
                now()->addMonths($documentType->default_validity_months)->toDateString() : null),
            'uploaded_by' => auth()->id(),
            'notes' => $request->notes,
        ]);

        return redirect()->route('admin.documents.index')
            ->with('success', 'Dokumen berhasil diupload.');
    }

    /**
     * Display the specified resource.
     */
    public function show(EmployeeDocument $document)
    {
        $document->load([
            'user:id,name,employee_id',
            'documentType:id,name,description',
            'uploadedBy:id,name',
        ]);

        return Inertia::render('admin/Documents/Show', [
            'document' => $document,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(EmployeeDocument $document)
    {
        $users = User::select('id', 'name', 'employee_id')->orderBy('name')->get();
        $documentTypes = DocumentType::select('id', 'name', 'code', 'requires_expiry', 'allowed_extensions', 'max_file_size_mb')
            ->active()
            ->orderBy('name')
            ->get();

        return Inertia::render('admin/Documents/Edit', [
            'document' => $document,
            'users' => $users,
            'documentTypes' => $documentTypes,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, EmployeeDocument $document)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'issued_date' => ['nullable', 'date', 'before_or_equal:today'],
            'expiry_date' => ['nullable', 'date', 'after:issued_date'],
            'notes' => ['nullable', 'string'],
        ]);

        $document->update($validated);

        return redirect()->route('admin.documents.index')
            ->with('success', 'Dokumen berhasil diperbarui.');
    }


    /**
     * Download document file
     */
    public function download(EmployeeDocument $document)
    {
        if (! Storage::disk('private')->exists($document->file_path)) {
            abort(404, 'File tidak ditemukan.');
        }

        return Storage::disk('private')->download(
            $document->file_path,
            $document->file_name
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(EmployeeDocument $document)
    {
        $document->delete();

        return back()->with('success', 'Dokumen berhasil dihapus.');
    }
}
