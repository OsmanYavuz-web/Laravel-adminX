<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class BackupService
{
    protected string $backupDir = 'backups';

    /**
     * Create a backup based on type: 'db', 'files', or 'full'.
     */
    public function createBackup(string $type = 'full'): string
    {
        $disk = Storage::disk('local');
        if (! $disk->exists($this->backupDir)) {
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
        if (! $disk->exists($this->backupDir)) {
            return [];
        }

        $files = $disk->files($this->backupDir);
        $backups = [];

        foreach ($files as $file) {
            $filename = basename($file);
            $fullPath = $disk->path($file);

            if (! File::exists($fullPath)) {
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

        usort($backups, fn ($a, $b) => $b['created_at'] <=> $a['created_at']);

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
        $zip = new ZipArchive;
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
            $dbConnection = config('database.default');
            $dbPath = config('database.connections.'.$dbConnection.'.database');

            if (File::exists($dbPath)) {
                if ($dbConnection === 'sqlite') {
                    $zip->addFile($dbPath, 'database.sqlite');
                } else {
                    $tempSql = storage_path('app/temp_dump_'.uniqid().'.sql');
                    try {
                        $this->runDatabaseDump($dbConnection, $tempSql);
                        $zip->addFile($tempSql, 'database_dump.sql');
                    } finally {
                        if (File::exists($tempSql)) {
                            File::delete($tempSql);
                        }
                    }
                }
            }

            $zip->close();
        }
    }

    /**
     * Helper to create Storage files zip.
     */
    protected function createFilesZip(string $zipPath): void
    {
        $zip = new ZipArchive;
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
            $privateMedia = storage_path('app/private/media');
            if (File::exists($privateMedia)) {
                $files = File::allFiles($privateMedia);
                foreach ($files as $file) {
                    $relativePath = 'media/'.$file->getRelativePathname();
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
        $zip = new ZipArchive;
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
            // Add database
            $dbPath = config('database.connections.sqlite.database');
            if (File::exists($dbPath)) {
                $zip->addFile($dbPath, 'database.sqlite');
            }

            // Add private media files
            $privateMedia = storage_path('app/private/media');
            if (File::exists($privateMedia)) {
                $files = File::allFiles($privateMedia);
                foreach ($files as $file) {
                    $relativePath = 'private/media/'.$file->getRelativePathname();
                    $zip->addFile($file->getRealPath(), $relativePath);
                }
            }

            $zip->close();
        }
    }

    /**
     * Run a database dump to a temp file.
     */
    protected function runDatabaseDump(string $connection, string $outputPath): void
    {
        $db = DB::connection($connection);
        $pdo = $db->getPdo();
        $driver = $db->getDriverName();

        if ($driver === 'mysql') {
            $dump = '';
            $tables = $pdo->query('SHOW TABLES')->fetchAll(\PDO::FETCH_COLUMN);
            foreach ($tables as $table) {
                $create = $pdo->query("SHOW CREATE TABLE `{$table}`")->fetch(\PDO::FETCH_ASSOC);
                $dump .= "--\n-- Table: {$table}\n--\n\n";
                $dump .= $create['Create Table'].";\n\n";
                $rows = $pdo->query("SELECT * FROM `{$table}`")->fetchAll(\PDO::FETCH_ASSOC);
                foreach ($rows as $row) {
                    $cols = array_map(fn ($v) => is_null($v) ? 'NULL' : $pdo->quote($v), array_values($row));
                    $dump .= "INSERT INTO `{$table}` VALUES (".implode(', ', $cols).");\n";
                }
                $dump .= "\n";
            }
            File::put($outputPath, $dump);
        } elseif ($driver === 'pgsql') {
            $dump = '';
            $tables = $pdo->query("SELECT tablename FROM pg_tables WHERE schemaname = 'public'")->fetchAll(\PDO::FETCH_COLUMN);
            foreach ($tables as $table) {
                $dump .= "--\n-- Table: {$table}\n--\n\n";
                $rows = $pdo->query("SELECT * FROM \"{$table}\"")->fetchAll(\PDO::FETCH_ASSOC);
                foreach ($rows as $row) {
                    $cols = array_map(fn ($v) => is_null($v) ? 'NULL' : $pdo->quote($v), array_values($row));
                    $dump .= "INSERT INTO \"{$table}\" VALUES (".implode(', ', $cols).");\n";
                }
                $dump .= "\n";
            }
            File::put($outputPath, $dump);
        } else {
            File::put($outputPath, "-- Database dump not supported for driver: {$driver}");
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

        return round($bytes, $precision).' '.$units[$pow];
    }
}
