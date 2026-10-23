<?php
declare(strict_types=1);

/**
 * This is an educational heuristic, not a forensic AI detector.
 * It produces a probability-like score from visible file characteristics.
 */
function analyze_image(string $path, string $mime): array
{
    $score = 50;
    $reasons = [];
    $info = @getimagesize($path);

    if ($info === false) {
        return ['score' => 0, 'verdict' => 'Invalid image', 'reasons' => ['Image decoding failed']];
    }

    $width = (int) ($info[0] ?? 0);
    $height = (int) ($info[1] ?? 0);
    $pixels = $width * $height;

    if ($pixels >= 4_000_000) {
        $score += 10;
        $reasons[] = 'Large, high-resolution image';
    } elseif ($pixels < 200_000) {
        $score -= 8;
        $reasons[] = 'Small image dimensions';
    }

    if ($width > 0 && $height > 0) {
        $ratio = $width / $height;
        if (abs($ratio - 1.0) < 0.03 || abs($ratio - 1.7778) < 0.03 || abs($ratio - 1.5) < 0.03) {
            $score += 5;
            $reasons[] = 'Common generated-image aspect ratio';
        }
    }

    $exif = @exif_read_data($path, null, true);
    if (!$exif) {
        $score += 12;
        $reasons[] = 'No EXIF camera metadata detected';
    } else {
        $score -= 10;
        $reasons[] = 'Camera metadata is present';
    }

    $size = filesize($path) ?: 0;
    $bytesPerPixel = $pixels > 0 ? $size / $pixels : 0;
    if ($bytesPerPixel < 0.35 && in_array($mime, ['image/jpeg', 'image/webp'], true)) {
        $score += 8;
        $reasons[] = 'Strong compression relative to pixel count';
    }

    $score = max(0, min(100, $score));
    if ($score >= 65) {
        $verdict = 'Possibly AI-generated';
    } elseif ($score <= 35) {
        $verdict = 'Possibly camera-originated';
    } else {
        $verdict = 'Inconclusive';
    }

    return ['score' => $score, 'verdict' => $verdict, 'reasons' => $reasons];
}
