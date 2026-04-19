<?php
/**
 * Skaliert und komprimiert ein Bild.
 * @param string $sourcePath Temporärer Pfad des hochgeladenen Bildes
 * @param string $targetPath Zielpfad auf dem Server
 * @param int $maxWidth Maximale Breite in Pixeln
 * @param int $quality Bildqualität (0-100)
 * @return bool True bei Erfolg, sonst False
 */
function resizeAndCompressImage($sourcePath, $targetPath, $maxWidth = 1920, $quality = 80)
{
    // Prüfen, ob die GD-Bibliothek auf dem Server aktiviert ist
    if (!extension_loaded('gd'))
        return false;

    $info = @getimagesize($sourcePath);
    if (!$info)
        return false;

    $mime = $info['mime'];
    $width = $info[0];
    $height = $info[1];

    // Nur verkleinern, wenn das Bild breiter als $maxWidth ist
    if ($width > $maxWidth) {
        $newWidth = $maxWidth;
        $newHeight = (int) (($height / $width) * $maxWidth);
    } else {
        $newWidth = $width;
        $newHeight = $height;
    }

    $image = null;
    switch ($mime) {
        case 'image/jpeg':
            $image = @imagecreatefromjpeg($sourcePath);
            break;
        case 'image/png':
            $image = @imagecreatefrompng($sourcePath);
            break;
        case 'image/webp':
            $image = @imagecreatefromwebp($sourcePath);
            break;
        default:
            return false; // Nicht unterstütztes Format
    }

    if (!$image)
        return false;

    $newImage = imagecreatetruecolor($newWidth, $newHeight);

    // Transparenz für PNG und WebP erhalten
    if ($mime == 'image/png' || $mime == 'image/webp') {
        imagealphablending($newImage, false);
        imagesavealpha($newImage, true);
        $transparent = imagecolorallocatealpha($newImage, 255, 255, 255, 127);
        imagefilledrectangle($newImage, 0, 0, $newWidth, $newHeight, $transparent);
    }

    imagecopyresampled($newImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

    $success = false;
    switch ($mime) {
        case 'image/jpeg':
            $success = imagejpeg($newImage, $targetPath, $quality);
            break;
        case 'image/png':
            // PNG nutzt ein Kompressionslevel von 0 bis 9
            $pngQuality = round((100 - $quality) / 10);
            $success = imagepng($newImage, $targetPath, max(0, min(9, $pngQuality)));
            break;
        case 'image/webp':
            $success = imagewebp($newImage, $targetPath, $quality);
            break;
    }

    imagedestroy($image);
    imagedestroy($newImage);

    return $success;
}