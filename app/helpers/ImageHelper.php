<?php

class ImageHelper {
    // Constants
    const MAX_SIZE = 2 * 1024 * 1024; // 2MB
    const ALLOWED_TYPES = ['image/jpeg', 'image/png', 'image/jpg'];
    const MAX_WIDTH = 1920;
    const MAX_HEIGHT = 1080;
    const COMPRESSION_QUALITY = 70;
    
    /**
     * Upload and process image
     * 
     * @param array $file $_FILES['input_name']
     * @param string $destinationFolder Relative path from public folder (e.g. 'uploads/gambar/')
     * @return string Relative path to saved file
     * @throws Exception
     */
    public static function upload($file, $destinationFolder = 'uploads/gambar/') {
        // 1. Basic Validation
        if (!isset($file['error']) || is_array($file['error'])) {
            throw new Exception('Invalid parameters.');
        }

        switch ($file['error']) {
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_NO_FILE:
                throw new Exception('No file sent.');
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                throw new Exception('Exceeded filesize limit.');
            default:
                throw new Exception('Unknown errors.');
        }

        // 2. Type Validation
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);
        
        if (!in_array($mimeType, self::ALLOWED_TYPES)) {
            throw new Exception('Invalid file format. Only JPG and PNG allowed.');
        }

        // 3. Prepare Destination
        $publicPath = ROOT_PATH . '/public/';
        $targetDir = $publicPath . $destinationFolder;
        
        if (!is_dir($targetDir)) {
            if (!mkdir($targetDir, 0755, true)) {
                throw new Exception('Failed to create upload directory.');
            }
        }

        // 4. Processing (Resize & Compress if needed)
        // Generate unique name
        $extension = array_search($mimeType, [
            'jpg' => 'image/jpeg',
            'png' => 'image/png'
        ], true);
        
        if ($extension === false) {
             // Fallback extension detection
             $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        }
        
        $filename = time() . '_' . bin2hex(random_bytes(8)) . '.' . $extension;
        $targetPath = $targetDir . $filename;
        
        // Check size and dimensions
        $sourceImage = null;
        if ($mimeType == 'image/jpeg') {
            $sourceImage = @imagecreatefromjpeg($file['tmp_name']);
        } elseif ($mimeType == 'image/png') {
            $sourceImage = @imagecreatefrompng($file['tmp_name']);
        }

        if (!$sourceImage) {
             // Fallback to simple move if GD fails or invalid image
            if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
                throw new Exception('Failed to save file.');
            }
            return $destinationFolder . $filename;
        }

        // Get dimensions
        $width = imagesx($sourceImage);
        $height = imagesy($sourceImage);
        
        // Calculate new dimensions
        $newWidth = $width;
        $newHeight = $height;
        $resizeNeeded = false;

        if ($width > self::MAX_WIDTH || $height > self::MAX_HEIGHT) {
            $ratio = $width / $height;
            if ($width / self::MAX_WIDTH > $height / self::MAX_HEIGHT) {
                $newWidth = self::MAX_WIDTH;
                $newHeight = $newWidth / $ratio;
            } else {
                $newHeight = self::MAX_HEIGHT;
                $newWidth = $newHeight * $ratio;
            }
            $resizeNeeded = true;
        }

        // Compress if size > 2MB or Resize needed
        if ($file['size'] > self::MAX_SIZE || $resizeNeeded) {
            $newImage = imagecreatetruecolor($newWidth, $newHeight);
            
            // Preserve transparency for PNG
            if ($mimeType == 'image/png') {
                imagealphablending($newImage, false);
                imagesavealpha($newImage, true);
                $transparent = imagecolorallocatealpha($newImage, 255, 255, 255, 127);
                imagefilledrectangle($newImage, 0, 0, $newWidth, $newHeight, $transparent);
            }

            imagecopyresampled($newImage, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
            
            // Save
            $saved = false;
            if ($mimeType == 'image/jpeg') {
                $saved = imagejpeg($newImage, $targetPath, self::COMPRESSION_QUALITY);
            } else { // PNG
                // PNG quality is 0-9, where 0 is no compression. We map 70% roughly to 6? 
                // Actually imagepng quality is compression level. 
                // We'll stick to standard PNG save, maybe no explicit quality manipulation for PNG to keep it simple or convert to JPG?
                // Request said "maintain... quality 70%". Converting PNG to JPG loses transparency.
                // We'll keep PNG format but maybe compress? PHP imagepng doesn't accept quality %, it accepts compression level 0-9.
                $saved = imagepng($newImage, $targetPath, 6);
            }
            
            imagedestroy($sourceImage);
            imagedestroy($newImage);

            if (!$saved) {
                 throw new Exception('Failed to save compressed image.');
            }

        } else {
            // No processing needed, just move
            imagedestroy($sourceImage);
             if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
                throw new Exception('Failed to save file.');
            }
        }

        return '/' . $destinationFolder . $filename;
    }

    /**
     * Delete image file
     * @param string $relativePath
     */
    public static function delete($relativePath) {
        if (empty($relativePath)) return;
        
        $fullPath = ROOT_PATH . '/public' . $relativePath;
        if (file_exists($fullPath) && is_file($fullPath)) {
            unlink($fullPath);
        }
    }
}
