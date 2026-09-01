<?php
/****************************************************************************\

svg-qrcode.php - Generate SVG QR Codes. MIT license.

(c) Phil Ronan, 2025

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER
DEALINGS IN THE SOFTWARE.

\****************************************************************************/

require(__DIR__ . '/qrcode.php');

class SVGQRCode extends QRCode {

    public function output_svg() {
        $image = $this->render_svg();
        header('Content-Type: image/svg+xml');
        die($image);
    }

    public function render_svg() {
        list($code, $widths, $width, $height, $x, $y, $w, $h) = $this->encode_and_calculate_size($this->data, $this->options);
        // We only care about the array $code['b'] at this stage. This contains the
        // ones and zeros corresponding to the QR image.
        $fgcolor = "000";
        $bgcolor = "fff";
        $scale = 10;
        $safe_zone = 2;
        $code_size = count($code['b']);
        $qr_version = ($code_size - 17) >> 2;
        $canvas_size = ($code_size + 2 * $safe_zone) * $scale;

        $xml = "<?xml version=\"1.0\" standalone=\"no\"?>\n";
        $xml .= "<svg width=\"$canvas_size\" height=\"$canvas_size\" version=\"1.1\" ";
        $xml .= "viewBox=\"0 0 $canvas_size $canvas_size\" xmlns=\"http://www.w3.org/2000/svg\">\n";
        $xml .= "<defs>\n";
        $xml .= "<symbol id=\"finder\" width=\"70\" height=\"70\" viewBox=\"0 0 70 70\">\n";
        $xml .= "<path d=\"M69.375,20L69.375,50C69.375,60.694 60.694,69.375 50,69.375L20,69.375C9.306,69.375 0.625,60.694 0.625,50L0.625,20C0.625,9.306 9.306,0.625 20,0.625L50,0.625C60.694,0.625 69.375,9.306 69.375,20ZM60.625,20C60.625,14.136 55.864,9.375 50,9.375L20,9.375C14.136,9.375 9.375,14.136 9.375,20L9.375,50C9.375,55.864 14.136,60.625 20,60.625L50,60.625C55.864,60.625 60.625,55.864 60.625,50L60.625,20ZM49.375,25L49.375,45C49.375,47.416 47.416,49.375 45,49.375L25,49.375C22.584,49.375 20.625,47.416 20.625,45L20.625,25C20.625,22.584 22.584,20.625 25,20.625L45,20.625C47.416,20.625 49.375,22.584 49.375,25Z\"/>\n";
        $xml .= "</symbol>\n";
        $xml .= "<symbol id=\"alignment\" width=\"50\" height=\"50\" viewBox=\"0 0 50 50\">\n";
        $xml .= "<path d=\"M48.75,19L48.75,31C48.75,40.796 40.796,48.75 31,48.75L19,48.75C9.204,48.75 1.25,40.796 1.25,31L1.25,19C1.25,9.204 9.204,1.25 19,1.25L31,1.25C40.796,1.25 48.75,9.204 48.75,19ZM41.25,19C41.25,13.343 36.658,8.75 31,8.75L19,8.75C13.343,8.75 8.75,13.343 8.75,19L8.75,31C8.75,36.658 13.343,41.25 19,41.25L31,41.25C36.658,41.25 41.25,36.658 41.25,31L41.25,19ZM25,18.75C28.45,18.75 31.25,21.55 31.25,25C31.25,28.45 28.45,31.25 25,31.25C21.55,31.25 18.75,28.45 18.75,25C18.75,21.55 21.55,18.75 25,18.75Z\"/>\n";
        $xml .= "</symbol>\n";
        $xml .= "</defs>\n";
        $xml .= "<g id=\"qr-code\"><g>\n";
        // $xml .= "<rect x=\"0\" y=\"0\" width=\"$canvas_size\" height=\"$canvas_size\" fill=\"#$bgcolor\"/>\n";
        $xml .= "<g fill=\"#000\">\n";

        // Add the alignment patterns
        if ($qr_version >= 2) {
			$alignment = $this->qr_alignment_patterns[$qr_version - 2];
            $n = count($alignment);
            for ($p=0; $p<$n; $p++) {
                for ($q=0; $q<$n; $q++) {
                    if (($p==0 && $q==0) || ($p==$n-1 && $q==0) || ($p==0 && $q==$n-1)) continue;
                    $tx = $alignment[$p];
                    $ty = $alignment[$q];

                    // blank out the pixels set under this pattern
                    for ($xx =-2; $xx <= 2; $xx++) {
                        for ($yy =-2; $yy <= 2; $yy++) {
                            $code['b'][$ty+$yy][$tx+$xx] = 0;
                        }
                    }

                    // Add this shape as a replacement
                    $cx = ($safe_zone + $tx - 2) * $scale;
                    $cy = ($safe_zone + $ty - 2) * $scale;
                    $xml .= "<use href=\"#alignment\" x=\"$cx\" y=\"$cy\"/>\n";
                }
            }
        }

        // Drop in the finder patterns at three corners
        for ($dy=0; $dy<7; $dy++) {
            for ($dx=0; $dx<7; $dx++) {
                $code['b'][$dy][$dx] = 0;
                $code['b'][$dy + $code_size - 7][$dx] = 0;
                $code['b'][$dy][$dx + $code_size - 7] = 0;
            }
        }
        $x0 = $y0 = $safe_zone * $scale;
        $x1 = $y1 = ($safe_zone + $code_size - 7) * $scale;
        $xml .= "<use href=\"#finder\" x=\"$x0\" y=\"$y0\"/>\n";
        $xml .= "<use href=\"#finder\" x=\"$x1\" y=\"$y0\"/>\n";
        $xml .= "<use href=\"#finder\" x=\"$x0\" y=\"$y1\"/>\n";
        $xml .= "</g>\n";
        $xml .= "<g fill=\"#$fgcolor\">\n";
        for ($y=0; $y<$code_size; $y++) {
            for ($x=0; $x<$code_size; $x++) {
                if ($code['b'][$y][$x]) {
                    $cx = ($safe_zone + $x + 0.5) * $scale;
                    $cy = ($safe_zone + $y + 0.5) * $scale;
                    $r = 0.4 * $scale;
                    $xml .= "<circle cx=\"$cx\" cy=\"$cy\" r=\"$r\"/>\n";
                }
            }
        }
        $xml .= "</g>\n";
        $xml .= "</g></g>\n";
        $xml .= "</svg>\n";
        return $xml;
    }
}

if (realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME'])) {
    // Test
    $data = @$_GET['d'] or "https://github.com/philronan/php-svg-qrcode";
    $gen = new SVGQRCode($data, $options=array('s'=>'qrm'));
    $svg = $gen->render_svg();
    die($svg);
}


