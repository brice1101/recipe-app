<?php

namespace App;

use finfo;
use Random\RandomException;
use RuntimeException;

class ImageUploader
{
    private const ALLOWED_MIME = ['image/jpeg', 'image/png'];
    private const MAX_BYTES = 5 * 1024 * 1024; // 5MB
    private const UPLOAD_DIR = __DIR__ . '/../public/uploads/';

    /**
     * Validate and move an uploaded image.
     * Return the relative web path on success
     * Return \RuntimeException on failure
     *
     * @param array $file Entry from $_FILES
     *
     * @throws RandomException
     */
    public static function store(array $file): string {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new RuntimeException(self::uploadErrorMessage($file['error']));
        }

        if ($file['size'] > self::MAX_BYTES) {
            throw new RuntimeException('File is too large');
        }

        // use finfo for mime type detection
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);

        if (!in_array($mime, self::ALLOWED_MIME, true)) {
            throw new RuntimeException('Invalid file type');
        }

        $ext = self::mimeToExt($mime);
        $filename = bin2hex(random_bytes(16)) . ".$ext";
        $dest = self::UPLOAD_DIR . $filename;

        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            throw new RuntimeException('Failed to move uploaded file');
        }

        return "/uploads/$filename";
    }

    /*
     * Delete a stored image by its relative web path.
     * Silently ignores non-existent files.
     */
    public static function delete(string $relativePath): void {
        $full = __DIR__ . '/../public' . ltrim($relativePath, '/');
        if (file_exists($full)) {
            unlink($full);
        }
    }

    private static function mimeToExt(string $mime): string {
        return match ($mime) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
        };
    }

    private static function uploadErrorMessage(int $code): string {
        return match ($code) {
            UPLOAD_ERR_INI_SIZE => 'The uploaded file is too large',
            UPLOAD_ERR_PARTIAL => 'The uploaded file was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            default => 'Upload failed (error ' . $code . ')',
        };
    }
}