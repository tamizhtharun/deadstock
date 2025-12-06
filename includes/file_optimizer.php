<?php
require_once '../vendor/autoload.php';

use setasign\Fpdi\Fpdi;

class FileOptimizer {

    /**
     * Optimize image file by resizing and compressing
     * @param string $sourcePath Source image path
     * @param string $destPath Destination path
     * @param int $maxWidth Maximum width (default 1200)
     * @param int $quality JPEG quality (default 80)
     * @return bool Success status
     */
    public static function optimizeImage($sourcePath, $destPath, $maxWidth = 1200, $quality = 80) {
        // Check if GD is available
        if (!extension_loaded('gd')) {
            // If GD not available, just copy the file
            return copy($sourcePath, $destPath);
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

        // Resize image
        imagecopyresampled($newImage, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        // Save optimized image
        $success = false;
        switch ($mime) {
            case 'image/jpeg':
                $success = imagejpeg($newImage, $destPath, $quality);
                break;
            case 'image/png':
                $success = imagepng($newImage, $destPath, 9); // Maximum compression for PNG
                break;
            case 'image/gif':
                $success = imagegif($newImage, $destPath);
                break;
        }

        // Free memory
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
     * @return string|bool Optimized filename or false on failure
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
            // Optimize image
            $success = self::optimizeImage($tempPath, $destPath);
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
