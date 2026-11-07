<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'category',
        'file_path',
        'file_name',
        'file_size',
        'mime_type',
        'description',
    ];

    /**
     * Relationship to the user who uploaded the document.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Human readable file size (e.g. 1.25 MB).
     */
    public function getFormattedSizeAttribute(): string
    {
        $bytes = $this->file_size;
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        }
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        }
        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 1) . ' KB';
        }
        return $bytes . ' B';
    }

    /**
     * Human readable category name.
     */
    public function getCategoryLabelAttribute(): string
    {
        return match ($this->category) {
            'surat_masuk' => 'Surat Masuk',
            'surat_keluar' => 'Surat Keluar',
            'nota_dinas' => 'Nota Dinas',
            'laporan' => 'Laporan',
            'invoice' => 'Invoice / Keuangan',
            default => 'Lainnya',
        };
    }

    /**
     * Category badge style classes.
     */
    public function getCategoryBadgeClassAttribute(): string
    {
        return match ($this->category) {
            'surat_masuk' => 'badge-blue',
            'surat_keluar' => 'badge-green',
            'nota_dinas' => 'badge-purple',
            'laporan' => 'badge-amber',
            'invoice' => 'badge-rose',
            default => 'badge-gray',
        };
    }

    /**
     * Category color hex for charts or native UI.
     */
    public function getCategoryColorAttribute(): string
    {
        return match ($this->category) {
            'surat_masuk' => '#2563eb',
            'surat_keluar' => '#059669',
            'nota_dinas' => '#7c3aed',
            'laporan' => '#d97706',
            'invoice' => '#e11d48',
            default => '#4b5563',
        };
    }

    /**
     * Get document file extension.
     */
    public function getExtensionAttribute(): string
    {
        return strtolower(pathinfo($this->file_name, PATHINFO_EXTENSION));
    }
}

