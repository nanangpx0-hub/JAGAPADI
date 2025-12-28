<?php
/**
 * Image Compressor Helper
 * Handles automatic image compression for uploads
 */

class ImageCompressor {
    
    private $maxSize = 2097152; // 2MB in bytes
    private $quality = 80; // JPEG quality (0-100) - 80% as per requirement
    private $maxWidth = 1920; // Max width in pixels
    private $maxHeight = 1920; // Max height in pixels
    
    /**
     * Compress image if needed
     * 
     * @param string $sourcePath Path to source image
     * @param string $destinationPath Path to save compressed image
     * @param int $maxSize Maximum file size in bytes
     * @return array Result with success status and info
     */
    public function compress($sourcePath, $destinationPath, $maxSize = null) {
        if ($maxSize !== null) {
            $this->maxSize = $maxSize;
        }
        
        // Check if file exists
        if (!file_exists($sourcePath)) {
            return [
                'success' => false,
                'error' => 'Source file not found'
            ];
        }
        
        // Get original file size
        $originalSize = filesize($sourcePath);
        
        // If file is already under limit, just copy it
        if ($originalSize <= $this->maxSize) {
            copy($sourcePath, $destinationPath);
            return [
                'success' => true,
                'compressed' => false,
                'original_size' => $originalSize,
                'final_size' => $originalSize,
                'message' => 'File size is acceptable, no compression needed'
            ];
        }
        
        // Get image info
        $imageInfo = getimagesize($sourcePath);
        if ($imageInfo === false) {
            return [
                'success' => false,
                'error' => 'Invalid image file'
            ];
        }
        
        list($width, $height, $type) = $imageInfo;
        
        // Create image resource from source
        $sourceImage = $this->createImageResource($sourcePath, $type);
        if ($sourceImage === false) {
            return [
                'success' => false,
                'error' => 'Failed to create image resource'
            ];
        }
        
        // Calculate new dimensions while maintaining aspect ratio
        $newDimensions = $this->calculateDimensions($width, $height);
        
        // Create new image with new dimensions
        $newImage = imagecreatetruecolor($newDimensions['width'], $newDimensions['height']);
        
        // Preserve transparency for PNG
        if ($type == IMAGETYPE_PNG) {
            imagealphablending($newImage, false);
            imagesavealpha($newImage, true);
            $transparent = imagecolorallocatealpha($newImage, 255, 255, 255, 127);
            imagefilledrectangle($newImage, 0, 0, $newDimensions['width'], $newDimensions['height'], $transparent);
        }
        
        // Resample image
        imagecopyresampled(
            $newImage, $sourceImage,
            0, 0, 0, 0,
            $newDimensions['width'], $newDimensions['height'],
            $width, $height
        );
        
        // Try different quality levels until file size is acceptable
        $quality = $this->quality;
        $attempts = 0;
        $maxAttempts = 5;
        
        do {
            // Save to temporary file
            $tempFile = $destinationPath . '.tmp';
            $saved = $this->saveImage($newImage, $tempFile, $type, $quality);
            
            if (!$saved) {
                imagedestroy($sourceImage);
                imagedestroy($newImage);
                return [
                    'success' => false,
                    'error' => 'Failed to save compressed image'
                ];
            }
            
            $newSize = filesize($tempFile);
            
            // If size is acceptable, use this file
            if ($newSize <= $this->maxSize) {
                rename($tempFile, $destinationPath);
                imagedestroy($sourceImage);
                imagedestroy($newImage);
                
                return [
                    'success' => true,
                    'compressed' => true,
                    'original_size' => $originalSize,
                    'final_size' => $newSize,
                    'reduction_percent' => round((($originalSize - $newSize) / $originalSize) * 100, 2),
                    'quality' => $quality,
                    'dimensions' => $newDimensions,
                    'message' => 'Image compressed successfully'
                ];
            }
            
            // Reduce quality for next attempt
            $quality -= 10;
            $attempts++;
            
            if (file_exists($tempFile)) {
                unlink($tempFile);
            }
            
        } while ($quality >= 50 && $attempts < $maxAttempts);
        
        // If still too large, try converting to JPEG with lower quality
        if ($type != IMAGETYPE_JPEG) {
            $quality = 70;
            $tempFile = $destinationPath . '.tmp';
            
            imagejpeg($newImage, $tempFile, $quality);
            $newSize = filesize($tempFile);
            
            if ($newSize <= $this->maxSize) {
                rename($tempFile, $destinationPath);
                imagedestroy($sourceImage);
                imagedestroy($newImage);
                
                return [
                    'success' => true,
                    'compressed' => true,
                    'converted_to_jpeg' => true,
                    'original_size' => $originalSize,
                    'final_size' => $newSize,
                    'reduction_percent' => round((($originalSize - $newSize) / $originalSize) * 100, 2),
                    'quality' => $quality,
                    'dimensions' => $newDimensions,
                    'message' => 'Image converted to JPEG and compressed'
                ];
            }
        }
        
        // Cleanup
        imagedestroy($sourceImage);
        imagedestroy($newImage);
        if (file_exists($tempFile)) {
            unlink($tempFile);
        }
        
        return [
            'success' => false,
            'error' => 'Unable to compress image to acceptable size'
        ];
    }
    
    /**
     * Create image resource from file
     */
    private function createImageResource($path, $type) {
        switch ($type) {
            case IMAGETYPE_JPEG:
                return imagecreatefromjpeg($path);
            case IMAGETYPE_PNG:
                return imagecreatefrompng($path);
            case IMAGETYPE_GIF:
                return imagecreatefromgif($path);
            case IMAGETYPE_WEBP:
                return imagecreatefromwebp($path);
            default:
                return false;
        }
    }
    
    /**
     * Save image to file
     */
    private function saveImage($image, $path, $type, $quality) {
        switch ($type) {
            case IMAGETYPE_JPEG:
                return imagejpeg($image, $path, $quality);
            case IMAGETYPE_PNG:
                // PNG quality is 0-9 (compression level)
                $pngQuality = round(9 - ($quality / 100 * 9));
                return imagepng($image, $path, $pngQuality);
            case IMAGETYPE_GIF:
                return imagegif($image, $path);
            case IMAGETYPE_WEBP:
                return imagewebp($image, $path, $quality);
            default:
                // Default to JPEG
                return imagejpeg($image, $path, $quality);
        }
    }
    
    /**
     * Calculate new dimensions maintaining aspect ratio
     */
    private function calculateDimensions($width, $height) {
        $ratio = $width / $height;
        
        // If image is already smaller than max dimensions, keep original size
        if ($width <= $this->maxWidth && $height <= $this->maxHeight) {
            return ['width' => $width, 'height' => $height];
        }
        
        // Calculate new dimensions
        if ($width > $height) {
            $newWidth = min($width, $this->maxWidth);
            $newHeight = round($newWidth / $ratio);
        } else {
            $newHeight = min($height, $this->maxHeight);
            $newWidth = round($newHeight * $ratio);
        }
        
        return [
            'width' => (int)$newWidth,
            'height' => (int)$newHeight
        ];
    }
    
    /**
     * Format file size for display
     */
    public static function formatFileSize($bytes) {
        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return round($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }
}
