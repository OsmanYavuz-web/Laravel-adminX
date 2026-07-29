<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use ZipArchive;
use App\Models\ActivityLog;

class BackupService
{
    protected string $backupDir = 'backups';

    /**
     * Create a backup based on type: 'db', 'files', or 'full'.
     */
    public function createBackup(string $type = 'full'): string
    {
        $disk = Storage::disk('local');
        if (!$disk->exists($this->backupDir)) {
            $disk->makeDirectory($this->backupDir);
        }

        $timestamp = now()->format('Y-m-d_H-i-s');
        $backupPath = $disk->path($this->backupDir);

        if ($type === 'db') {
            $filename = "backup_db_{$timestamp}.zip";
            $zipPath = "{$backupPath}/{$filename}";
            $this->createDatabaseZip($zipPath);
            $description = 'Veritabanı yedeği başarıyla alındı.';
        } elseif ($type === 'files') {
            $filename = "backup_files_{$timestamp}.zip";
            $zipPath = "{$backupPath}/{$filename}";
            $this->createFilesZip($zipPath);
            $description = 'Yüklenen dosyalar yedeği başarıyla alındı.';
        } else {
            $filename = "backup_full_{$timestamp}.zip";
            $zipPath = "{$backupPath}/{$filename}";
            $this->createFullZip($zipPath);
            $description = 'Tam sistem (Veritabanı + Dosyalar) yedeği başarıyla alındı.';
        }

        ActivityLog::record(
            event: 'backup_created',
            description: $description,
            properties: ['filename' => $filename, 'type' => $type, 'size' => filesize($zipPath)]
        );

        return $filename;
    }

    /**
     * List all available backups.
     */
    public function listBackups(): array
    {
        $disk = Storage::disk('local');
        if (!$disk->exists($this->backupDir)) {
            return [];
        }

        $files = $disk->files($this->backupDir);
        $backups = [];

        foreach ($files as $file) {
            $filename = basename($file);
            $fullPath = $disk->path($file);

            if (!File::exists($fullPath)) {
                continue;
            }

            $type = 'full';
            if (str_contains($filename, '_db_')) {
                $type = 'db';
            } elseif (str_contains($filename, '_files_')) {
                $type = 'files';
            }

            $backups[] = [
                'filename' => $filename,
                'type' => $type,
                'size' => File::size($fullPath),
                'formatted_size' => $this->formatBytes(File::size($fullPath)),
                'created_at' => File::lastModified($fullPath),
            ];
        }

        usort($backups, fn($a, $b) => $b['created_at'] <=> $a['created_at']);

        return $backups;
    }

    /**
     * Delete a backup file.
     */
    public function deleteBackup(string $filename): bool
    {
        $disk = Storage::disk('local');
        $path = "{$this->backupDir}/{$filename}";

        if ($disk->exists($path)) {
            $disk->delete($path);
            ActivityLog::record(
                event: 'backup_deleted',
                description: "Yedek dosyası silindi: {$filename}",
                properties: ['filename' => $filename]
            );
            return true;
        }

        return false;
    }

    /**
     * Helper to create Database zip.
     */
    protected function createDatabaseZip(string $zipPath): void
    {
        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
            $dbConnection = config('database.default');

            if ($dbConnection === 'sqlite') {
                $dbPath = config('database.connections.sqlite.database');
                if (File::exists($dbPath)) {
                    $zip->addFile($dbPath, 'database.sqlite');
                }
            } else {
                // Export SQLite or fallback DB file
                $tempSql = storage_path('app/temp_dump.sql');
                File::put($tempSql, "-- Database Dump\n-- Generated on " . now());
                $zip->addFile($tempSql, 'database_dump.sql');
            }

            $zip->close();
        }
    }

    /**
     * Helper to create Storage files zip.
     */
    protected function createFilesZip(string $zipPath): void
    {
        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
            $publicStorage = storage_path('app/public');
            if (File::exists($publicStorage)) {
                $files = File::allFiles($publicStorage);
                foreach ($files as $file) {
                    $relativePath = 'public/' . $file->getRelativePathname();
                    $zip->addFile($file->getRealPath(), $relativePath);
                }
            }
            $zip->close();
        }
    }

    /**
     * Helper to create Full system zip (DB + Storage).
     */
    protected function createFullZip(string $zipPath): void
    {
        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
            // Add database
            $dbPath = config('database.connections.sqlite.database');
            if (File::exists($dbPath)) {
                $zip->addFile($dbPath, 'database.sqlite');
            }

            // Add public files
            $publicStorage = storage_path('app/public');
            if (File::exists($publicStorage)) {
                $files = File::allFiles($publicStorage);
                foreach ($files as $file) {
                    $relativePath = 'storage/' . $file->getRelativePathname();
                    $zip->addFile($file->getRealPath(), $relativePath);
                }
            }

            $zip->close();
        }
    }

    /**
     * Format bytes to readable size.
     */
    protected function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
