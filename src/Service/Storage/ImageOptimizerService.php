<?php

namespace App\Service\Storage;

use Imagine\Gd\Imagine;
use Imagine\Image\Box;
use Imagine\Image\ImageInterface;

class ImageOptimizerService
{
    private Imagine $imagine;

    public function __construct()
    {
        $this->imagine = new Imagine();
    }

    public function optimize(string $sourcePath, string $targetPath, array $options = []): void
    {
        $image = $this->imagine->open($sourcePath);

        // Auto-rotate based on EXIF
        // Note: Imagine handles some of this, but we might need explicit handling if metadata is stripped
        // For now, assume Imagine handles basic opening correctly.

        // Resize
        if (isset($options['max_width']) || isset($options['max_height'])) {
            $size = $image->getSize();
            $ratio = $size->getWidth() / $size->getHeight();
            
            $width = $options['max_width'] ?? $size->getWidth();
            $height = $options['max_height'] ?? $size->getHeight();

            if ($width / $height > $ratio) {
                $width = $height * $ratio;
            } else {
                $height = $width / $ratio;
            }

            $image->resize(new Box($width, $height));
        }

        // Save with compression
        $saveOptions = [
            'quality' => $options['quality'] ?? 80
        ];

        // Format conversion (e.g., to WebP)
        if (isset($options['format']) && $options['format'] === 'webp') {
            $saveOptions['webp_quality'] = $options['quality'] ?? 80;
        }

        $image->save($targetPath, $saveOptions);
    }
    
    public function getDimensions(string $path): array
    {
        try {
            $image = $this->imagine->open($path);
            $size = $image->getSize();
            return [$size->getWidth(), $size->getHeight()];
        } catch (\Exception $e) {
            return [0, 0];
        }
    }
}
