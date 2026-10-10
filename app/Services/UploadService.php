<?php

namespace App\Services;

use CodeIgniter\HTTP\Files\UploadedFile;
use RuntimeException;

class UploadService
{
    private const MAX_BYTES = 2_000_000;

    public function store(?UploadedFile $file, string $folder, int $maxWidth = 1200, int $maxHeight = 1200): ?string
    {
        if ($file === null || $file->getError() === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        if (! $file->isValid() || $file->getSize() <= 0 || $file->getSize() > self::MAX_BYTES) {
            throw new RuntimeException('The image must be a valid file no larger than 2 MB.');
        }
        $mime = $file->getMimeType();
        if (! in_array($mime, ['image/jpeg', 'image/png', 'image/gif', 'image/webp'], true)) {
            throw new RuntimeException('Use a JPG, PNG, GIF, or WEBP image.');
        }
        $dimensions = @getimagesize($file->getTempName());
        if ($dimensions === false || $dimensions[0] < 1 || $dimensions[1] < 1
            || $dimensions[0] > 6000 || $dimensions[1] > 6000
            || $dimensions[0] * $dimensions[1] > 24_000_000) {
            throw new RuntimeException('The image dimensions are invalid or too large.');
        }
        $raw = file_get_contents($file->getTempName());
        $source = $raw === false ? false : @imagecreatefromstring($raw);
        if ($source === false) {
            throw new RuntimeException('The upload is not a readable image.');
        }
        $width = imagesx($source);
        $height = imagesy($source);
        $scale = min(1, $maxWidth / max(1, $width), $maxHeight / max(1, $height));
        $newWidth = max(1, (int) round($width * $scale));
        $newHeight = max(1, (int) round($height * $scale));
        $target = imagecreatetruecolor($newWidth, $newHeight);
        if ($target === false) {
            imagedestroy($source);
            throw new RuntimeException('The image could not be prepared.');
        }
        $white = imagecolorallocate($target, 255, 255, 255);
        imagefill($target, 0, 0, $white);
        imagecopyresampled($target, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        $name = bin2hex(random_bytes(16)) . '.jpg';
        $directory = FCPATH . 'uploads' . DIRECTORY_SEPARATOR . $folder;
        if (! is_dir($directory) && ! mkdir($directory, 0755, true) && ! is_dir($directory)) {
            imagedestroy($source);
            imagedestroy($target);
            throw new RuntimeException('The upload directory is not writable.');
        }
        if (! imagejpeg($target, $directory . DIRECTORY_SEPARATOR . $name, 86)) {
            $partial = $directory . DIRECTORY_SEPARATOR . $name;
            if (is_file($partial)) {
                unlink($partial);
            }
            imagedestroy($source);
            imagedestroy($target);
            throw new RuntimeException('The image could not be saved.');
        }
        imagedestroy($source);
        imagedestroy($target);
        return $name;
    }

    public function delete(?string $folder, ?string $name): void
    {
        if (! $folder || ! $name || basename($name) !== $name) {
            return;
        }
        $path = FCPATH . 'uploads' . DIRECTORY_SEPARATOR . $folder . DIRECTORY_SEPARATOR . $name;
        if (is_file($path)) {
            unlink($path);
        }
    }
}
