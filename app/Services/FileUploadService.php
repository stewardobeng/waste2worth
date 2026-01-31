<?php

namespace App\Services;

class FileUploadService
{
    private $uploadDir;

    public function __construct()
    {
        $this->uploadDir = __DIR__ . '/../../storage/uploads/';
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
    }

    public function upload($file, $userId, $entity = null, $entityId = null)
    {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return false;
        }

        $allowedTypes = ['image/jpeg', 'image/png', 'application/pdf'];
        if (!in_array($file['type'], $allowedTypes)) {
            return false;
        }

        $filename = uniqid('upload_') . '_' . basename($file['name']);
        $targetPath = $this->uploadDir . $filename;

        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            // Logic to save to file_uploads table could go here or in the caller
            return '/storage/uploads/' . $filename;
        }

        return false;
    }
}
