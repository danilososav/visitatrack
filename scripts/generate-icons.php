<?php

// One-off script to generate simple placeholder PWA icons (blue rounded square, "VT" mark).
// Run once via: php scripts/generate-icons.php

function makeIcon(int $size, string $path): void
{
    $im = imagecreatetruecolor($size, $size);
    imagesavealpha($im, true);
    $transparent = imagecolorallocatealpha($im, 0, 0, 0, 127);
    imagefill($im, 0, 0, $transparent);

    $bg = imagecolorallocate($im, 15, 23, 42); // slate-900, matches theme_color
    $radius = (int) ($size * 0.22);
    imagefilledrectangle($im, $radius, 0, $size - $radius - 1, $size - 1, $bg);
    imagefilledrectangle($im, 0, $radius, $size - 1, $size - $radius - 1, $bg);
    imagefilledellipse($im, $radius, $radius, $radius * 2, $radius * 2, $bg);
    imagefilledellipse($im, $size - $radius - 1, $radius, $radius * 2, $radius * 2, $bg);
    imagefilledellipse($im, $radius, $size - $radius - 1, $radius * 2, $radius * 2, $bg);
    imagefilledellipse($im, $size - $radius - 1, $size - $radius - 1, $radius * 2, $radius * 2, $bg);

    $accent = imagecolorallocate($im, 59, 130, 246); // blue-500
    $pinW = (int) ($size * 0.34);
    $cx = (int) ($size / 2);
    $cy = (int) ($size * 0.40);
    imagefilledellipse($im, $cx, $cy, $pinW, $pinW, $accent);
    $points = [
        $cx - (int) ($pinW * 0.42), $cy + (int) ($pinW * 0.12),
        $cx + (int) ($pinW * 0.42), $cy + (int) ($pinW * 0.12),
        $cx, (int) ($size * 0.74),
    ];
    imagefilledpolygon($im, $points, $accent);

    $white = imagecolorallocate($im, 255, 255, 255);
    $holeR = (int) ($pinW * 0.28);
    imagefilledellipse($im, $cx, $cy, $holeR * 2, $holeR * 2, $white);

    imagepng($im, $path);
    imagedestroy($im);
}

$dir = __DIR__.'/../public/icons';
if (! is_dir($dir)) {
    mkdir($dir, 0755, true);
}

makeIcon(192, $dir.'/icon-192.png');
makeIcon(512, $dir.'/icon-512.png');
makeIcon(512, $dir.'/icon-512-maskable.png');
makeIcon(180, $dir.'/apple-touch-icon.png');

echo "Icons generated in {$dir}\n";
