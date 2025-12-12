<?php
require_once '../vendor/autoload.php';

use setasign\Fpdi\Fpdi;

class FileOptimizer {

    /**
     * Optimize image file by resizing and converting to WebP
     * @param string $sourcePath Source image path
     * @param string $destPath Destination path (will be changed to .webp extension)
     * @param int $maxWidth Maximum width (default 1200)
     * @param int $quality WebP quality (default 85)
     * @return bool Success status
     */
    public static function optimizeImage($sourcePath, $destPath, $maxWidth = 1200, $quality = 85) {
        // Check if GD is available
        if (!extension_loaded('gd')) {
            // If GD not available, just copy the file
            return copy($sourcePath, $destPath);
        }

        // Check if WebP support is available
        if (!function_exists('imagewebp')) {
            // If WebP not supported, fall back to original format
            error_log('WebP not supported, falling back to original format');
            return self::optimizeImageLegacy($sourcePath, $destPath, $maxWidth, $quality);
        }

        // Get image info
        $imageInfo = getimagesize($sourcePath);
        if (!$imageInfo) {
            return false;
        }

        $width = $imageInfo[0];
        $height = $imageInfo[1];
        $mime = $imageInfo['mime'];

        // Calculate new dimensions
        if ($width > $maxWidth) {
            $newWidth = $maxWidth;
            $newHeight = intval($height * ($maxWidth / $width));
        } else {
            $newWidth = $width;
            $newHeight = $height;
        }

        // Create new image
        $newImage = imagecreatetruecolor($newWidth, $newHeight);

        // Load source image based on type
        switch ($mime) {
            case 'image/jpeg':
                $sourceImage = imagecreatefromjpeg($sourcePath);
                break;
            case 'image/png':
                $sourceImage = imagecreatefrompng($sourcePath);
                // Preserve transparency for PNG
                imagealphablending($newImage, false);
                imagesavealpha($newImage, true);
                $transparent = imagecolorallocatealpha($newImage, 0, 0, 0, 127);
                imagefill($newImage, 0, 0, $transparent);
                break;
            case 'image/gif':
                $sourceImage = imagecreatefromgif($sourcePath);
                // Preserve transparency for GIF
                imagealphablending($newImage, false);
                imagesavealpha($newImage, true);
                $transparent = imagecolorallocatealpha($newImage, 0, 0, 0, 127);
                imagefill($newImage, 0, 0, $transparent);
                break;
            default:
                return false;
        }

        if (!$sourceImage) {
            return false;
        }

        // Resize image
        imagecopyresampled($newImage, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        // Change destination to .webp extension
        $destPath = preg_replace('/\.(jpg|jpeg|png|gif)$/i', '.webp', $destPath);

        // Save as WebP
        $success = imagewebp($newImage, $destPath, $quality);

        // Free memory
        imagedestroy($sourceImage);
        imagedestroy($newImage);

        return $success;
    }

    /**
     * Legacy image optimization (fallback when WebP is not available)
     * @param string $sourcePath Source image path
     * @param string $destPath Destination path
     * @param int $maxWidth Maximum width
     * @param int $quality Quality
     * @return bool Success status
     */
    private static function optimizeImageLegacy($sourcePath, $destPath, $maxWidth = 1200, $quality = 80) {
        $imageInfo = getimagesize($sourcePath);
        if (!$imageInfo) {
            return false;
        }

        $width = $imageInfo[0];
        $height = $imageInfo[1];
        $mime = $imageInfo['mime'];

        if ($width > $maxWidth) {
            $newWidth = $maxWidth;
            $newHeight = intval($height * ($maxWidth / $width));
        } else {
            $newWidth = $width;
            $newHeight = $height;
        }

        $newImage = imagecreatetruecolor($newWidth, $newHeight);

        switch ($mime) {
            case 'image/jpeg':
                $sourceImage = imagecreatefromjpeg($sourcePath);
                break;
            case 'image/png':
                $sourceImage = imagecreatefrompng($sourcePath);
                imagealphablending($newImage, false);
                imagesavealpha($newImage, true);
                break;
            case 'image/gif':
                $sourceImage = imagecreatefromgif($sourcePath);
                break;
            default:
                return false;
        }

        if (!$sourceImage) {
            return false;
        }

        imagecopyresampled($newImage, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        $success = false;
        switch ($mime) {
            case 'image/jpeg':
                $success = imagejpeg($newImage, $destPath, $quality);
                break;
            case 'image/png':
                $success = imagepng($newImage, $destPath, 9);
                break;
            case 'image/gif':
                $success = imagegif($newImage, $destPath);
                break;
        }

        imagedestroy($sourceImage);
        imagedestroy($newImage);

        return $success;
    }

    /**
     * Optimize PDF file by compressing it
     * @param string $sourcePath Source PDF path
     * @param string $destPath Destination path
     * @return bool Success status
     */
    public static function optimizePDF($sourcePath, $destPath) {
        try {
            $pdf = new Fpdi();

            // Get total pages
            $pageCount = $pdf->setSourceFile($sourcePath);

            // Import all pages
            for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                $templateId = $pdf->importPage($pageNo);
                $size = $pdf->getTemplateSize($templateId);

                // Create new page with same size
                $pdf->AddPage($size['orientation'], array($size['width'], $size['height']));
                $pdf->useTemplate($templateId);
            }

            // Output compressed PDF
            $pdf->Output($destPath, 'F');

            return true;
        } catch (Exception $e) {
            // If optimization fails, copy original file
            return copy($sourcePath, $destPath);
        }
    }

    /**
     * Process uploaded file (image or PDF) and optimize it
     * @param array $file $_FILES array element
     * @param string $uploadDir Upload directory
     * @param string $filename Desired filename
     * @return string|bool Optimized filename (with .webp extension for images) or false on failure
     */
    public static function processUploadedFile($file, $uploadDir, $filename) {
        $tempPath = $file['tmp_name'];
        $fileType = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        // Ensure upload directory exists
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $destPath = $uploadDir . $filename;

        // Process based on file type
        if (in_array($fileType, ['jpg', 'jpeg', 'png', 'gif'])) {
            // Optimize image (will convert to WebP)
            $success = self::optimizeImage($tempPath, $destPath);
            // Return the actual filename with .webp extension
            if ($success) {
                $filename = preg_replace('/\.(jpg|jpeg|png|gif)$/i', '.webp', $filename);
            }
        } elseif ($fileType === 'pdf') {
            // Optimize PDF
            $success = self::optimizePDF($tempPath, $destPath);
        } else {
            // For other files, just move
            $success = move_uploaded_file($tempPath, $destPath);
        }

        return $success ? $filename : false;
    }
}
?>
