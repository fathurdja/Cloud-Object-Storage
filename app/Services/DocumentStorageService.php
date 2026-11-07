<?php

namespace App\Services;

use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentStorageService
{
    /**
     * Get the active disk name.
     */
    public function getDiskName(): string
    {
        return config('filesystems.default', 'rustfs');
    }

    /**
     * Store an uploaded document to storage.
     * Path pattern: uploads/{category}/{Y-m-d}_{uuid}.{extension}
     *
     * @return array{file_path: string, disk: string}
     * @throws Exception
     */
    public function storeDocument(UploadedFile $file, string $category): array
    {
        $primaryDisk = $this->getDiskName();
        $dateStr = date('Y-m-d');
        $uuid = Str::uuid()->toString();
        $ext = $file->getClientOriginalExtension() ?: 'bin';
        $filename = "{$dateStr}_{$uuid}.{$ext}";
        $directory = "uploads/{$category}";
        $targetPath = "{$directory}/{$filename}";

        // Attempt upload to primary disk (e.g., rustfs)
        try {
            $stream = fopen($file->getRealPath(), 'r+');
            $success = Storage::disk($primaryDisk)->put($targetPath, $stream, [
                'ContentType' => $file->getClientMimeType(),
            ]);

            if (is_resource($stream)) {
                fclose($stream);
            }

            if ($success) {
                return [
                    'file_path' => $targetPath,
                    'disk' => $primaryDisk,
                ];
            }
        } catch (\Throwable $e) {
            Log::warning("Storage upload failed on [{$primaryDisk}]: " . $e->getMessage());

            // If primary disk was rustfs and failed (e.g. Docker not started yet),
            // fallback to local disk so user work is not interrupted.
            if ($primaryDisk !== 'local') {
                try {
                    $file->storeAs($directory, $filename, 'local');
                    Log::info("Fallback upload succeeded on [local] disk for {$filename}");

                    return [
                        'file_path' => $targetPath,
                        'disk' => 'local',
                    ];
                } catch (\Throwable $localEx) {
                    throw new Exception("Gagal menyimpan file ke penyimpanan: " . $localEx->getMessage(), 0, $localEx);
                }
            }

            throw new Exception("Gagal menyimpan file ke {$primaryDisk}: " . $e->getMessage(), 0, $e);
        }

        throw new Exception("Gagal mengunggah file ke sistem penyimpanan.");
    }

    /**
     * Retrieve a download response or stream for a document.
     */
    public function getDownloadStream(string $filePath, ?string $originalName = null)
    {
        $primaryDisk = $this->getDiskName();

        // Check primary disk first
        if (Storage::disk($primaryDisk)->exists($filePath)) {
            return Storage::disk($primaryDisk)->download($filePath, $originalName);
        }

        // Check local disk fallback
        if (Storage::disk('local')->exists($filePath)) {
            return Storage::disk('local')->download($filePath, $originalName);
        }

        // Check public disk fallback
        if (Storage::disk('public')->exists($filePath)) {
            return Storage::disk('public')->download($filePath, $originalName);
        }

        throw new Exception("Berkas fisik tidak ditemukan di sistem penyimpanan.");
    }

    /**
     * Retrieve an inline response to view the document in the browser (PDF/images).
     */
    public function getInlineResponse(string $filePath, string $mimeType, string $originalName)
    {
        $primaryDisk = $this->getDiskName();
        $disk = null;

        if (Storage::disk($primaryDisk)->exists($filePath)) {
            $disk = $primaryDisk;
        } elseif (Storage::disk('local')->exists($filePath)) {
            $disk = 'local';
        } elseif (Storage::disk('public')->exists($filePath)) {
            $disk = 'public';
        }

        if (!$disk) {
            throw new Exception("Berkas fisik tidak ditemukan di sistem penyimpanan.");
        }

        return Storage::disk($disk)->response($filePath, $originalName, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="' . $originalName . '"',
        ]);
    }

    /**
     * Delete document from storage.
     */
    public function deleteDocument(string $filePath): bool
    {
        $primaryDisk = $this->getDiskName();
        $deleted = false;

        try {
            if (Storage::disk($primaryDisk)->exists($filePath)) {
                $deleted = Storage::disk($primaryDisk)->delete($filePath);
            }
        } catch (\Throwable $e) {
            Log::warning("Failed deleting from [{$primaryDisk}]: " . $e->getMessage());
        }

        // Also clean up local if exists
        try {
            if (Storage::disk('local')->exists($filePath)) {
                Storage::disk('local')->delete($filePath);
                $deleted = true;
            }
        } catch (\Throwable $e) {
            // Ignore
        }

        return $deleted;
    }

    /**
     * Check health/connectivity of the RustFS/S3 storage.
     *
     * @return array{healthy: bool, message: string, disk: string, endpoint: string}
     */
    public function checkHealth(): array
    {
        $disk = $this->getDiskName();
        $endpoint = config('filesystems.disks.rustfs.endpoint', 'http://127.0.0.1:9000');

        if ($disk !== 'rustfs' && $disk !== 's3') {
            return [
                'healthy' => true,
                'message' => "Menggunakan penyimpanan lokal ({$disk}).",
                'disk' => $disk,
                'endpoint' => 'Local Filesystem',
            ];
        }

        try {
            // Test ping or bucket list
            Storage::disk($disk)->files('uploads');

            return [
                'healthy' => true,
                'message' => "Terhubung dengan RustFS Object Storage.",
                'disk' => $disk,
                'endpoint' => $endpoint,
            ];
        } catch (\Throwable $e) {
            return [
                'healthy' => false,
                'message' => "RustFS belum aktif di {$endpoint}. (Fallback lokal aktif jika dibutuhkan)",
                'disk' => $disk,
                'endpoint' => $endpoint,
            ];
        }
    }
}

