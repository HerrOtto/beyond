<?php

class captcha
{

    // Apperence
    public $backgroundColor = array(
        255,
        255,
        255
    );

    public $colors = array(
        array(210, 40, 10), // red
        array(20, 160, 40), // green
        array(30, 80, 180), // blue
    );

    public $fonts = array(
        'Antykwa' => 'AntykwaBold.ttf',
        'Candice' => 'Candice.ttf',
        'DingDong' => 'Ding-DongDaddyO.ttf',
        'Duality' => 'Duality.ttf',
        'Heineken' => 'Heineken.ttf',
        'Jura' => 'Jura.ttf',
        'StayPuft' => 'StayPuft.ttf',
        'Times' => 'TimesNewRomanBold.ttf',
        'VeraSans' => 'VeraSansBold.ttf'
    );

    /**
     * Create captcha
     * @param int $width Width of image
     * @param int $height Width of image
     * @param int $minLength Min. number of characters
     * @param int $maxLength Max. number of characters
     * @param int $maxRotation Max. deg. of character rotation in deg (p.e. +/- 8deg).
     * @return mixed false on error otherwise array with image and text
     */
    public function CreateImage($width, $height, $minLength, $maxLength, $maxRotation)
    {
        try {

            // Create image
            $im = imagecreatetruecolor($width, $height);

            // Background color
            $backgroundColorGD = imagecolorallocatealpha($im,
                $this->backgroundColor[0],
                $this->backgroundColor[1],
                $this->backgroundColor[2],
                127
            );
            imagealphablending($im, false);
            imagesavealpha($im, true);
            imagefilledrectangle($im, 0, 0, $width, $height, $backgroundColorGD);
            imagealphablending($im, true);

            // Random text
            $captchaText = $this->RandomText(rand($minLength, $maxLength));

            // Wrtie captcha to image
            $this->WriteText(
                $im,
                $width,
                $height,
                $maxRotation,
                $captchaText
            );

            // Get image as PNG
            ob_start();
            imagepng($im);
            $pngFile = ob_get_contents();
            ob_end_clean();

            // Destroy
            imagedestroy($im);

            // Return image an captcha text
            return array(
                'text' => $captchaText,
                'image' => $pngFile
            );
        } catch (Exception $e) {
            // On error return false
            return false;
        }
    }

    /**
     * Random text generation
     * @param string $length Number of characters
     * @return string Random text
     */
    protected function RandomText($length)
    {
        $result = '';

        $vowels = array("a", "e", "i", "o", "u");
        $consonants = array(
            'b', 'c', 'd', 'f', 'g', 'h', 'j', 'k', 'l', 'm',
            'n', 'p', 'r', 's', 't', 'v', 'w', 'x', 'y', 'z'
        );
        for ($i = 1; $i <= ceil($length / 2); $i++) {
            $result .= $consonants[rand(0, count($consonants) - 1)];
            if (strlen($result) < $length) {
                $result .= $vowels[rand(0, count($vowels) - 1)];
            }
        }
        return $result;
    }

    /**
     * Render letters to image
     * @param object $im Image
     * @param int $width Image width
     * @param int $height Image height
     * @param int $maxRotation Max. deg. of character rotation in deg (p.e. +/- 8deg).
     * @param string $text Text to render
     */
    protected function WriteText(&$im, $width, $height, $maxRotation, $text)
    {
        if ($im === null) return;

        // Text generation (char by char)
        $x = 0;
        $y = 0;
        $i = 0;

        $colorNo = -1;
        for ($i = 0; $i < strlen($text); $i++) {
            $letter = substr($text, $i, 1);

            $degree = rand($maxRotation * -1, $maxRotation);

            do {
                $colorNoNew = mt_rand(0, sizeof($this->colors) - 1);
            } while ($colorNo == $colorNoNew);
            $colorNo = $colorNoNew;


            $color = $this->colors[$colorNo];
            $colorGD = imagecolorallocatealpha($im, $color[0], $color[1], $color[2], 30);

            $font = $this->fonts[array_rand($this->fonts)];
            $fontFile = __DIR__ . '/../fonts/' . $font;

            $size = imagettfbbox(
                $height * 0.5, // size
                $degree, // angel
                $fontFile, // fontfile
                $letter
            );

            $coords = imagettftext(
                $im,
                $height * 0.5,
                $degree,
                ($width*0.02) + (($width / (strlen($text) + 0.5)) * $i) + abs($size[0]),
                (($height - abs($size[5])) / 2) + abs($size[5]),
                $colorGD,
                $fontFile,
                $letter
            );
        }
    }

}
