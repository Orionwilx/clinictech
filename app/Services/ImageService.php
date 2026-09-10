<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Compresión de imágenes con GD: reduce el lado mayor y re-codifica a JPEG
 * para minimizar el peso de las evidencias fotográficas.
 */
class ImageService
{
    public function __construct(
        private readonly int $maxDimension = 1600,
        private readonly int $quality = 72,
    ) {}

    /**
     * Comprime la imagen y la guarda en el disco public.
     *
     * @return array{path: string, size: int}
     */
    public function storeCompressed(UploadedFile $file, string $directory): array
    {
        $image = @imagecreatefromstring((string) file_get_contents($file->getRealPath()));

        if ($image === false) {
            throw ValidationException::withMessages(['photo' => 'El archivo no es una imagen válida.']);
        }

        $image = $this->applyExifOrientation($image, $file);
        $image = $this->resize($image);
        $image = $this->flattenOnWhite($image);

        ob_start();
        imagejpeg($image, null, $this->quality);
        $binary = (string) ob_get_clean();
        imagedestroy($image);

        $path = trim($directory, '/').'/'.Str::uuid().'.jpg';
        Storage::disk('public')->put($path, $binary);

        return ['path' => $path, 'size' => strlen($binary)];
    }

    private function resize(\GdImage $image): \GdImage
    {
        $width = imagesx($image);
        $height = imagesy($image);
        $longest = max($width, $height);

        if ($longest <= $this->maxDimension) {
            return $image;
        }

        $scale = $this->maxDimension / $longest;
        $resized = imagescale($image, (int) round($width * $scale), (int) round($height * $scale));
        imagedestroy($image);

        return $resized ?: throw ValidationException::withMessages(['photo' => 'No se pudo procesar la imagen.']);
    }

    /**
     * Rota las fotos de celular según su metadato EXIF de orientación.
     */
    private function applyExifOrientation(\GdImage $image, UploadedFile $file): \GdImage
    {
        if (! function_exists('exif_read_data') || $file->getMimeType() !== 'image/jpeg') {
            return $image;
        }

        $exif = @exif_read_data($file->getRealPath());
        $angle = match ($exif['Orientation'] ?? 1) {
            3 => 180,
            6 => -90,
            8 => 90,
            default => 0,
        };

        if ($angle === 0) {
            return $image;
        }

        $rotated = imagerotate($image, $angle, 0);
        imagedestroy($image);

        return $rotated ?: $image;
    }

    /**
     * Aplana transparencias (PNG/WebP) sobre fondo blanco antes de pasar a JPEG.
     */
    private function flattenOnWhite(\GdImage $image): \GdImage
    {
        $width = imagesx($image);
        $height = imagesy($image);

        $flat = imagecreatetruecolor($width, $height);
        imagefill($flat, 0, 0, (int) imagecolorallocate($flat, 255, 255, 255));
        imagecopy($flat, $image, 0, 0, 0, 0, $width, $height);
        imagedestroy($image);

        return $flat;
    }
}
