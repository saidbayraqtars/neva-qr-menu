<?php

namespace App\Services;

/**
 * QR kodun ETRAFINA dekoratif çerçeve/desen çizer (GD ile kompozisyon).
 *
 * ÖNEMLİ: Süslemeler yalnızca QR'ın dış kenar boşluğunda kalır; QR modüllerinin
 * üzerine hiçbir şey çizilmez → kod her zaman taranabilir.
 */
class QrArtService
{
    private const W = 760;
    private const H = 820;
    private const Q = 480;                 // QR görüntüsünün boyutu
    private const QX = (self::W - self::Q) / 2;
    private const QY = 150;

    /** @return string PNG binary */
    public function compose(\GdImage $qr, array $design, string $label): string
    {
        $im = imagecreatetruecolor(self::W, self::H);
        imagesavealpha($im, true);
        imagealphablending($im, true);

        $paper  = $design['paper']  ?? '#FFFFFF';
        $accent = $design['accent'] ?? '#C8A96A';
        $ink    = $design['ink']    ?? '#0D0D07';
        $frame  = $design['frame']  ?? 'minimal';

        // Zemin (bilet çerçevesi için hafif farklı ton → kart etkisi)
        $bg = $frame === 'ticket' ? $this->mix($paper, $ink, 0.07) : $paper;
        imagefilledrectangle($im, 0, 0, self::W, self::H, $this->col($im, $bg));

        // Bilet: QR'dan ÖNCE açık kartı çiz (QR bunun üstüne biner)
        if ($frame === 'ticket') {
            $this->roundRectFilled($im, 44, 44, self::W - 44, self::H - 44, 30, $this->col($im, $paper));
        }

        // QR'ı yerleştir (boyutlandırarak)
        imagealphablending($im, true);
        imagecopyresampled($im, $qr, (int) self::QX, (int) self::QY, 0, 0, (int) self::Q, (int) self::Q, imagesx($qr), imagesy($qr));

        imageantialias($im, true);

        match ($frame) {
            'brush'    => $this->brush($im, $accent, $ink, $label),
            'floral'   => $this->floral($im, $accent, $ink, $label),
            'deco'     => $this->deco($im, $accent, $ink, $label),
            'scallop'  => $this->scallop($im, $accent, $ink, $label),
            'ticket'   => $this->ticket($im, $accent, $ink, $bg, $label),
            'seal'     => $this->seal($im, $accent, $ink),
            'halftone' => $this->halftone($im, $accent, $ink, $label),
            'bracket'  => $this->bracket($im, $accent, $ink, $label),
            'ribbon'   => $this->ribbon($im, $accent, $ink, $label),
            default    => $this->minimal($im, $accent, $ink, $label),
        };

        ob_start();
        imagepng($im, null, 6);
        $bytes = ob_get_clean();
        imagedestroy($im);

        return $bytes;
    }

    /* ============================ ÇERÇEVELER ============================ */

    private function brush(\GdImage $im, string $accent, string $ink, string $label): void
    {
        $a  = $this->col($im, $accent);
        $ad = $this->col($im, $this->mix($accent, '#000000', 0.28));
        [$x1, $y1, $x2, $y2] = [92, 100, self::W - 92, self::QY + self::Q + 20];

        mt_srand(41);
        $stroke = function (int $sx, int $sy, int $ex, int $ey, int $col, int $base) use ($im) {
            $steps = (int) (hypot($ex - $sx, $ey - $sy) / 4);
            for ($i = 0; $i <= $steps; $i++) {
                $t = $i / max(1, $steps);
                $px = (int) round($sx + ($ex - $sx) * $t + mt_rand(-4, 4));
                $py = (int) round($sy + ($ey - $sy) * $t + mt_rand(-4, 4));
                $taper = sin($t * M_PI);                     // uçlarda incelen
                $r = (int) ($base * (0.5 + 0.5 * $taper)) + mt_rand(-2, 3);
                imagefilledellipse($im, $px, $py, $r, $r, $col);
            }
        };
        // gölge + ana darbe, 4 kenar (köşelerden biraz taşarak)
        foreach ([[$ad, 30, 4], [$a, 26, 0]] as [$col, $b, $off]) {
            $stroke($x1 - 16 + $off, $y1 + $off, $x2 + 16 + $off, $y1 + $off, $col, $b);
            $stroke($x2 + $off, $y1 - 16 + $off, $x2 + $off, $y2 + 16 + $off, $col, $b);
            $stroke($x2 + 16 + $off, $y2 + $off, $x1 - 16 + $off, $y2 + $off, $col, $b);
            $stroke($x1 + $off, $y2 + 16 + $off, $x1 + $off, $y1 - 16 + $off, $col, $b);
        }
        $this->spaced($im, mb_strtoupper($label), $this->col($im, $ink), self::W / 2, self::H - 46, 15, 'sans', 6);
    }

    private function floral(\GdImage $im, string $accent, string $ink, string $label): void
    {
        $g  = $this->col($im, $accent);
        $gd = $this->col($im, $this->mix($accent, '#000000', 0.3));
        $bloom = $this->col($im, $this->mix($accent, '#FFFFFF', 0.15));
        [$x1, $y1, $x2, $y2] = [96, 104, self::W - 96, self::QY + self::Q + 22];

        imagesetthickness($im, 2);
        imagerectangle($im, $x1, $y1, $x2, $y2, $this->col($im, $this->mix($accent, '#FFFFFF', 0.4)));
        imagesetthickness($im, 1);

        // Köşede çeyrek daire yayı — QR'a doğru uzanmaz.
        $sprig = function (int $cx, int $cy, int $dx, int $dy) use ($im, $g, $gd, $bloom) {
            $pts = [];
            for ($i = 0; $i <= 12; $i++) {
                $ang = ($i / 12) * (M_PI / 2);
                $pts[] = [
                    (int) round($cx + $dx * cos($ang) * 46),
                    (int) round($cy + $dy * sin($ang) * 46),
                ];
            }
            imagesetthickness($im, 3);
            for ($i = 1; $i < count($pts); $i++) {
                imageline($im, $pts[$i - 1][0], $pts[$i - 1][1], $pts[$i][0], $pts[$i][1], $gd);
            }
            imagesetthickness($im, 1);
            // 3 küçük yaprak (mercek polygon) sap boyunca
            foreach ([[3, 1], [6, -1], [9, 1]] as [$k, $s]) {
                [$lx, $ly] = $pts[$k];
                imagefilledpolygon($im, [
                    $lx, $ly,
                    (int) ($lx + $dx * 9 + $s * 5), (int) ($ly - $dy * 9 + $s * 5),
                    (int) ($lx + $dx * 22), (int) ($ly - $dy * 6),
                    (int) ($lx + $dx * 10 - $s * 3), (int) ($ly + $dy * 4 - $s * 5),
                ], $g);
            }
            // küçük çiçek sap ucunda (köşeye yakın uç)
            [$fx, $fy] = $pts[1];
            for ($p = 0; $p < 5; $p++) {
                $ang = $p * 2 * M_PI / 5 - M_PI / 2;
                imagefilledellipse($im, (int) ($fx + cos($ang) * 8), (int) ($fy + sin($ang) * 8), 10, 10, $bloom);
            }
            imagefilledellipse($im, $fx, $fy, 8, 8, $gd);
        };
        $sprig($x1, $y1, 1, 1);
        $sprig($x2, $y1, -1, 1);
        $sprig($x1, $y2, 1, -1);
        $sprig($x2, $y2, -1, -1);

        $this->centered($im, $label, $this->col($im, $ink), self::W / 2, self::H - 44, 16, 'serif');
    }

    private function deco(\GdImage $im, string $accent, string $ink, string $label): void
    {
        $a = $this->col($im, $accent);
        foreach ([84, 74] as $ins) {
            imagesetthickness($im, $ins === 84 ? 3 : 1);
            imagerectangle($im, $ins, $ins - 40, self::W - $ins, self::QY + self::Q + 66, $a);
        }
        imagesetthickness($im, 2);
        $corner = function (int $cx, int $cy, int $dx, int $dy) use ($im, $a) {
            for ($i = 0; $i <= 5; $i++) {
                $ang = ($i / 5) * (M_PI / 2);
                imageline($im, $cx, $cy, (int) ($cx + $dx * cos($ang) * 58), (int) ($cy + $dy * sin($ang) * 58), $a);
            }
            imagefilledarc($im, $cx, $cy, 26, 26, $dx > 0 ? ($dy > 0 ? 0 : 270) : ($dy > 0 ? 90 : 180), $dx > 0 ? ($dy > 0 ? 90 : 360) : ($dy > 0 ? 180 : 270), $a, IMG_ARC_PIE);
        };
        $corner(90, 50, 1, 1);
        $corner(self::W - 90, 50, -1, 1);
        $corner(90, self::QY + self::Q + 60, 1, -1);
        $corner(self::W - 90, self::QY + self::Q + 60, -1, -1);
        imagesetthickness($im, 1);
        $this->spaced($im, mb_strtoupper($label), $a, self::W / 2, self::H - 40, 14, 'serif', 8);
    }

    private function scallop(\GdImage $im, string $accent, string $ink, string $label): void
    {
        $a = $this->col($im, $accent);
        $tint = $this->col($im, $this->mix($accent, '#FFFFFF', 0.82));
        [$x1, $y1, $x2, $y2] = [96, 104, self::W - 96, self::QY + self::Q + 22];
        $r = 15;
        imagesetthickness($im, 3);
        $edge = function (int $ax, int $ay, int $bx, int $by, int $ox, int $oy) use ($im, $a, $tint, $r) {
            $len = hypot($bx - $ax, $by - $ay);
            $n = max(1, (int) round($len / ($r * 2)));
            for ($i = 0; $i < $n; $i++) {
                $t = ($i + 0.5) / $n;
                $mx = (int) ($ax + ($bx - $ax) * $t + $ox * $r);
                $my = (int) ($ay + ($by - $ay) * $t + $oy * $r);
                imagefilledellipse($im, $mx, $my, $r * 2, $r * 2, $tint);
                imagearc($im, $mx, $my, $r * 2, $r * 2, 0, 360, $a);
            }
        };
        $edge($x1, $y1, $x2, $y1, 0, -1);
        $edge($x2, $y1, $x2, $y2, 1, 0);
        $edge($x2, $y2, $x1, $y2, 0, 1);
        $edge($x1, $y2, $x1, $y1, -1, 0);
        imagerectangle($im, $x1, $y1, $x2, $y2, $a);
        imagesetthickness($im, 1);
        $this->centered($im, $label, $this->col($im, $ink), self::W / 2, self::H - 42, 16, 'sans');
    }

    private function ticket(\GdImage $im, string $accent, string $ink, string $bg, string $label): void
    {
        $void = $this->col($im, $bg);
        $a = $this->col($im, $accent);
        [$x1, $y1, $x2, $y2] = [44, 44, self::W - 44, self::H - 44];
        // yan çentikler (kartı deler gibi — zemin rengiyle)
        $ny = (int) (($y1 + $y2) / 2);
        imagefilledellipse($im, $x1, $ny, 50, 50, $void);
        imagefilledellipse($im, $x2, $ny, 50, 50, $void);
        // üst/alt kesik çizgi
        $dash = $this->col($im, $this->mix($accent, '#FFFFFF', 0.3));
        for ($x = $x1 + 74; $x < $x2 - 74; $x += 16) {
            imagefilledrectangle($im, $x, $y1 + 34, $x + 8, $y1 + 36, $dash);
            imagefilledrectangle($im, $x, $y2 - 60, $x + 8, $y2 - 58, $dash);
        }
        $this->roundRectStroke($im, $x1 + 16, $y1 + 16, $x2 - 16, $y2 - 16, 20, $a, 2);
        $this->centered($im, '✦  ✦  ✦', $a, self::W / 2, $y1 + 26, 12, 'sans');
        $this->spaced($im, mb_strtoupper($label), $a, self::W / 2, self::H - 78, 15, 'sans', 7);
    }

    private function seal(\GdImage $im, string $accent, string $ink): void
    {
        $a = $this->col($im, $accent);
        $cx = (int) (self::W / 2);
        $cy = (int) (self::QY + self::Q / 2);
        imagesetthickness($im, 5);
        imagearc($im, $cx, $cy, 700, 700, 0, 360, $a);
        imagesetthickness($im, 2);
        imagearc($im, $cx, $cy, 636, 636, 0, 360, $a);
        imagesetthickness($im, 1);
        $this->curved($im, mb_strtoupper('Dijital Menü'), $a, $cx, $cy, 300, 180, 360, false, 17);
        $this->curved($im, mb_strtoupper('QR Kodunu Okut'), $a, $cx, $cy, 300, 180, 0, true, 17);
        foreach ([0, 180] as $deg) {
            $r = deg2rad($deg);
            imagefilledellipse($im, (int) ($cx + cos($r) * 318), (int) ($cy + sin($r) * 318), 12, 12, $a);
        }
    }

    private function halftone(\GdImage $im, string $accent, string $ink, string $label): void
    {
        $a = $this->col($im, $accent, 30);
        $cx = self::W / 2;
        $cy = self::QY + self::Q / 2;
        $safe = self::Q / 2 + 46;
        for ($gx = 24; $gx < self::W; $gx += 30) {
            for ($gy = 24; $gy < self::H - 30; $gy += 30) {
                $d = hypot($gx - $cx, $gy - $cy);
                if ($d < $safe) {
                    continue;
                }
                $r = (int) min(13, 2 + ($d - $safe) / 26);
                if ($r < 2) {
                    continue;
                }
                imagefilledellipse($im, $gx, $gy, $r, $r, $a);
            }
        }
        $this->centered($im, $label, $this->col($im, $ink), self::W / 2, self::H - 40, 16, 'sans');
    }

    private function bracket(\GdImage $im, string $accent, string $ink, string $label): void
    {
        $a = $this->col($im, $accent);
        [$x1, $y1, $x2, $y2] = [92, 100, self::W - 92, self::QY + self::Q + 20];
        imagesetthickness($im, 1);
        imagerectangle($im, $x1, $y1, $x2, $y2, $this->col($im, $this->mix($accent, '#FFFFFF', 0.55)));
        imagesetthickness($im, 3);
        $arm = 66;
        $b = function (int $cx, int $cy, int $dx, int $dy) use ($im, $a, $arm) {
            foreach ([0, 9] as $o) {
                imageline($im, $cx + $dx * $o, $cy + $dy * $o, $cx + $dx * ($o + $arm), $cy + $dy * $o, $a);
                imageline($im, $cx + $dx * $o, $cy + $dy * $o, $cx + $dx * $o, $cy + $dy * ($o + $arm), $a);
            }
            imagefilledellipse($im, $cx + $dx * 4, $cy + $dy * 4, 9, 9, $a);
        };
        $b($x1, $y1, 1, 1);
        $b($x2, $y1, -1, 1);
        $b($x1, $y2, 1, -1);
        $b($x2, $y2, -1, -1);
        imagesetthickness($im, 1);
        $this->spaced($im, mb_strtoupper($label), $this->col($im, $ink), self::W / 2, self::H - 42, 14, 'serif', 7);
    }

    private function ribbon(\GdImage $im, string $accent, string $ink, string $label): void
    {
        $a = $this->col($im, $accent);
        $ad = $this->col($im, $this->mix($accent, '#000000', 0.35));
        [$bx1, $bx2, $by1, $by2] = [80, self::W - 80, 44, 120];
        // gölge
        imagefilledrectangle($im, $bx1 + 6, $by1 + 8, $bx2 + 6, $by2 + 8, $ad);
        // banner + katlanmış uçlar
        imagefilledrectangle($im, $bx1, $by1, $bx2, $by2, $a);
        imagefilledpolygon($im, [$bx1, $by1, $bx1 - 30, (int) (($by1 + $by2) / 2), $bx1, $by2], $ad);
        imagefilledpolygon($im, [$bx2, $by1, $bx2 + 30, (int) (($by1 + $by2) / 2), $bx2, $by2], $ad);
        $this->spaced($im, mb_strtoupper($label), $this->col($im, '#FFFFFF'), self::W / 2, (int) (($by1 + $by2) / 2) + 8, 18, 'serif', 8);
        // QR çerçevesi
        imagesetthickness($im, 2);
        imagerectangle($im, 100, self::QY - 24, self::W - 100, self::QY + self::Q + 24, $a);
        imagesetthickness($im, 1);
        $this->centered($im, 'menü için okutun', $this->col($im, $this->mix($ink, '#FFFFFF', 0.35)), self::W / 2, self::H - 46, 13, 'sans');
    }

    private function minimal(\GdImage $im, string $accent, string $ink, string $label): void
    {
        imagefilledrectangle($im, (int) (self::W / 2 - 46), self::QY - 46, (int) (self::W / 2 + 46), self::QY - 42, $this->col($im, $accent));
        $this->centered($im, mb_strtolower($label), $this->col($im, $this->mix($ink, '#FFFFFF', 0.4)), self::W / 2, self::QY + self::Q + 74, 15, 'sans');
    }

    /* ============================ YARDIMCILAR ============================ */

    private function font(string $kind): string
    {
        $base = base_path('vendor/dompdf/dompdf/lib/fonts/');

        return $base.($kind === 'serif' ? 'DejaVuSerif-Bold.ttf' : 'DejaVuSans-Bold.ttf');
    }

    private function col(\GdImage $im, string $hex, int $alpha = 0): int
    {
        [$r, $g, $b] = sscanf(ltrim($hex, '#'), '%02x%02x%02x');

        return imagecolorallocatealpha($im, (int) $r, (int) $g, (int) $b, $alpha);
    }

    private function mix(string $a, string $b, float $t): string
    {
        [$r1, $g1, $b1] = sscanf(ltrim($a, '#'), '%02x%02x%02x');
        [$r2, $g2, $b2] = sscanf(ltrim($b, '#'), '%02x%02x%02x');

        return sprintf('#%02x%02x%02x',
            (int) round($r1 + ($r2 - $r1) * $t),
            (int) round($g1 + ($g2 - $g1) * $t),
            (int) round($b1 + ($b2 - $b1) * $t),
        );
    }

    private function centered(\GdImage $im, string $text, int $color, float $cx, int $y, float $size, string $font): void
    {
        $f = $this->font($font);
        $bb = imagettfbbox($size, 0, $f, $text);
        imagettftext($im, $size, 0, (int) ($cx - ($bb[2] - $bb[0]) / 2), $y, $color, $f, $text);
    }

    private function spaced(\GdImage $im, string $text, int $color, float $cx, int $y, float $size, string $font, int $gap): void
    {
        $f = $this->font($font);
        $chars = preg_split('//u', $text, -1, PREG_SPLIT_NO_EMPTY);
        $total = 0;
        $widths = [];
        foreach ($chars as $c) {
            $bb = imagettfbbox($size, 0, $f, $c);
            $w = $bb[2] - $bb[0];
            $widths[] = $w;
            $total += $w + $gap;
        }
        $x = $cx - $total / 2;
        foreach ($chars as $i => $c) {
            imagettftext($im, $size, 0, (int) $x, $y, $color, $f, $c);
            $x += $widths[$i] + $gap;
        }
    }

    private function curved(\GdImage $im, string $text, int $color, int $cx, int $cy, int $radius, int $startDeg, int $endDeg, bool $flip, float $size): void
    {
        $f = $this->font('serif');
        $chars = preg_split('//u', $text, -1, PREG_SPLIT_NO_EMPTY);
        $n = count($chars);
        if ($n === 0) {
            return;
        }
        for ($i = 0; $i < $n; $i++) {
            $t = $n === 1 ? 0.5 : $i / ($n - 1);
            $deg = $startDeg + ($endDeg - $startDeg) * $t;
            $rad = deg2rad($deg);
            $x = $cx + cos($rad) * $radius;
            $y = $cy + sin($rad) * $radius;
            $rot = $flip ? -($deg - 90) : -($deg + 90);
            imagettftext($im, $size, $rot, (int) $x, (int) $y, $color, $f, $chars[$i]);
        }
    }

    private function roundRectFilled(\GdImage $im, int $x1, int $y1, int $x2, int $y2, int $r, int $c): void
    {
        imagefilledrectangle($im, $x1 + $r, $y1, $x2 - $r, $y2, $c);
        imagefilledrectangle($im, $x1, $y1 + $r, $x2, $y2 - $r, $c);
        imagefilledarc($im, $x1 + $r, $y1 + $r, $r * 2, $r * 2, 180, 270, $c, IMG_ARC_PIE);
        imagefilledarc($im, $x2 - $r, $y1 + $r, $r * 2, $r * 2, 270, 360, $c, IMG_ARC_PIE);
        imagefilledarc($im, $x1 + $r, $y2 - $r, $r * 2, $r * 2, 90, 180, $c, IMG_ARC_PIE);
        imagefilledarc($im, $x2 - $r, $y2 - $r, $r * 2, $r * 2, 0, 90, $c, IMG_ARC_PIE);
    }

    private function roundRectStroke(\GdImage $im, int $x1, int $y1, int $x2, int $y2, int $r, int $c, int $th): void
    {
        imagesetthickness($im, $th);
        imagearc($im, $x1 + $r, $y1 + $r, $r * 2, $r * 2, 180, 270, $c);
        imagearc($im, $x2 - $r, $y1 + $r, $r * 2, $r * 2, 270, 360, $c);
        imagearc($im, $x1 + $r, $y2 - $r, $r * 2, $r * 2, 90, 180, $c);
        imagearc($im, $x2 - $r, $y2 - $r, $r * 2, $r * 2, 0, 90, $c);
        imageline($im, $x1 + $r, $y1, $x2 - $r, $y1, $c);
        imageline($im, $x1 + $r, $y2, $x2 - $r, $y2, $c);
        imageline($im, $x1, $y1 + $r, $x1, $y2 - $r, $c);
        imageline($im, $x2, $y1 + $r, $x2, $y2 - $r, $c);
        imagesetthickness($im, 1);
    }
}
