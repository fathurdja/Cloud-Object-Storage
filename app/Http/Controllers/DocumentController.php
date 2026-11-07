<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Services\DocumentStorageService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DocumentController extends Controller
{
    protected DocumentStorageService $storageService;

    public function __construct(DocumentStorageService $storageService)
    {
        $this->storageService = $storageService;
    }

    /**
     * Display a listing of documents.
     */
    public function index(Request $request): View
    {
        $query = Document::with('user')->latest();

        // Search by keyword (title or filename)
        if ($request->filled('q')) {
            $keyword = trim($request->input('q'));
            $query->where(function ($q) use ($keyword) {
                $q->where('title', 'like', "%{$keyword}%")
                  ->orWhere('file_name', 'like', "%{$keyword}%");
            });
        }

        // Filter by category
        if ($request->filled('category')) {
            $query->where('category', $request->input('category'));
        }

        $documents = $query->paginate(10)->withQueryString();

        // Summary statistics
        $stats = [
            'total' => Document::count(),
            'surat_masuk' => Document::where('category', 'surat_masuk')->count(),
            'surat_keluar' => Document::where('category', 'surat_keluar')->count(),
            'nota_dinas' => Document::where('category', 'nota_dinas')->count(),
            'lainnya' => Document::whereNotIn('category', ['surat_masuk', 'surat_keluar', 'nota_dinas'])->count(),
        ];

        // Storage status
        $storageHealth = $this->storageService->checkHealth();

        return view('documents.index', compact('documents', 'stats', 'storageHealth'));
    }

    /**
     * Show document upload form.
     */
    public function create(): View
    {
        $storageHealth = $this->storageService->checkHealth();

        return view('documents.create', compact('storageHealth'));
    }

    /**
     * Store a newly created document.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'in:surat_masuk,surat_keluar,nota_dinas,laporan,invoice,lainnya'],
            'description' => ['nullable', 'string', 'max:1000'],
            'file' => [
                'required',
                'file',
                'max:10240', // Max 10MB
                'mimes:pdf,docx,doc,xlsx,xls,csv,jpg,jpeg,png',
            ],
        ], [
            'title.required' => 'Judul dokumen wajib diisi.',
            'category.required' => 'Kategori dokumen wajib dipilih.',
            'category.in' => 'Kategori yang dipilih tidak valid.',
            'file.required' => 'Berkas dokumen wajib diunggah.',
            'file.max' => 'Ukuran berkas tidak boleh melebihi 10 MB.',
            'file.mimes' => 'Format berkas hanya diperbolehkan PDF, DOCX, XLSX, XLS, CSV, JPG, PNG.',
        ]);

        $uploadedFile = $request->file('file');

        try {
            // Upload to RustFS / Storage
            $uploadResult = $this->storageService->storeDocument($uploadedFile, $request->input('category'));

            // Create record
            $document = Document::create([
                'user_id' => Auth::id(),
                'title' => $request->input('title'),
                'category' => $request->input('category'),
                'description' => $request->input('description'),
                'file_path' => $uploadResult['file_path'],
                'file_name' => $uploadedFile->getClientOriginalName(),
                'file_size' => $uploadedFile->getSize(),
                'mime_type' => $uploadedFile->getClientMimeType() ?: 'application/octet-stream',
            ]);

            $diskNote = $uploadResult['disk'] === 'rustfs' ? ' (Tersimpan di RustFS Object Storage)' : ' (Tersimpan di Local Storage)';

            return redirect()->route('documents.index')
                ->with('success', "Dokumen '{$document->title}' berhasil diunggah{$diskNote}.");
        } catch (Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Gagal mengunggah dokumen: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified document detail.
     */
    public function show(Document $document): View
    {
        $document->load('user');
        $storageHealth = $this->storageService->checkHealth();

        return view('documents.show', compact('document', 'storageHealth'));
    }

    /**
     * Download document file.
     */
    public function download(Document $document)
    {
        try {
            return $this->storageService->getDownloadStream($document->file_path, $document->file_name);
        } catch (Exception $e) {
            return back()->with('error', 'Gagal mengunduh berkas: ' . $e->getMessage());
        }
    }

    /**
     * Preview document inline in browser (for PDF, PNG, JPG).
     */
    public function preview(Document $document)
    {
        try {
            return $this->storageService->getInlineResponse($document->file_path, $document->mime_type, $document->file_name);
        } catch (Exception $e) {
            return back()->with('error', 'Gagal membuka pratinjau berkas: ' . $e->getMessage());
        }
    }

    /**
     * Delete document (Admin only).
     */
    public function destroy(Document $document): RedirectResponse
    {
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Akses Ditolak: Anda tidak memiliki wewenang untuk menghapus dokumen ini.');
        }

        $title = $document->title;

        try {
            $this->storageService->deleteDocument($document->file_path);
            $document->delete();

            return redirect()->route('documents.index')
                ->with('success', "Dokumen '{$title}' berhasil dihapus dari sistem.");
        } catch (Exception $e) {
            return back()->with('error', 'Gagal menghapus dokumen: ' . $e->getMessage());
        }
    }
}

