<?php

/**
 * Standart captcha type
 * This file is part of SergSXM UI package
 *
 * @package    SergSXM UI
 * @author     SergSXM <sergsxm@embedded.by>
 * @copyright  2015 SergSXM
 */

namespace Sergsxm\UIBundle\CaptchaTypes;

use Sergsxm\UIBundle\Form\Captcha;

class Standart extends Captcha
{

/**
 * Get type of form input
 * 
 * @return string Type
 */
    public function getType()
    {
        return 'standart';
    }
    
/**
 * Get default template for input
 * 
 * @return string Default template
 */
    public function getDefaultTemplate()
    {
        return 'SergsxmUIBundle:CaptchaTypes:Standart.html.twig';
    }
    
/**
 * Set configuration to default values
 */
    public function setDefaults()
    {
        $this->configuration = array(
            'description' => 'captcha',
            'validateError' => 'Values do not match',
            'width' => 150,
            'height' => 50,
            'background' => 'fff',
            'color' => '000',
            'noise' => false,
            'length' => 6,
            'letters' => '123456789ABCDEFGHIJKLMNPQRSTUVWXYZ',
            'font' => null,
        );
    }

/**
 * Validate session value with $value
 * 
 * @param string $value Value for comparation
 * @return boolean There are no errors
 */
    public function validateValue($value)
    {
        $strtoupper = (function_exists('mb_strtoupper') ? 'mb_strtoupper' : 'strtoupper');
        if (($this->getValue() == '') || ($this->getValue() !== $strtoupper(trim($value)))) {
            $this->error = $this->configuration['validateError'];
            return false;
        }
        
        $this->error = null;
        return true;
    }

/**
 * Get unique value for captcha
 * 
 * @return string Unique value
 */    
    public function getUniqueValue()
    {
        $value = '';
        $letters = $this->configuration['letters'];
        for ($i = 0; $i < $this->configuration['length']; $i++) {
            if (function_exists('mb_substr')) {
                $value .= mb_substr($letters, rand(0, mb_strlen($letters) - 1), 1);
            } else {
                $value .= substr($letters, rand(0, strlen($letters) - 1), 1);
            }
        }
        
        return $value;
    }

/**
 * Convert HTML color to RGB array
 * 
 * @param string $color HTML color string
 * @return array(3) RGB array
 */    
    private function colorHexToRgb($color) 
    {
        if ($color[0] == '#') {
            $color = substr($color, 1);
        }
        if (strlen($color) == 6) {
            list($r, $g, $b) = array($color[0].$color[1], $color[2].$color[3], $color[4].$color[5]);
        } elseif (strlen($color) == 3) {
            list($r, $g, $b) = array($color[0].$color[0], $color[1].$color[1], $color[2].$color[2]);
        } else {
            return array(null, null, null);
        }
        $r = @hexdec($r);
        $g = @hexdec($g);
        $b = @hexdec($b);
        return array($r, $g, $b);
    }
    
/**
 * Get HTML tags with imaged captcha value
 * 
 * @return string HTML tags
 */    
    public function getValueTag()
    {
        $value = $this->getValue();
        
        $image = imagecreate($this->configuration['width'], $this->configuration['height']);
        
        $fontSize = min($this->configuration['height'] / 1.5, $this->configuration['width'] / (strlen($value) + 2));
        if ($fontSize < 8) {
            $fontSize = 8;
        }
        
        list($r, $g, $b) = $this->colorHexToRgb($this->configuration['background']);
        if ($r === null) {
            $r = 0; 
            $g = 0; 
            $b = 0;
        }
        $backColor = imagecolorallocate($image, $r, $g, $b);
        
        list($r, $g, $b) = $this->colorHexToRgb($this->configuration['color']);
        if ($r === null) {
            $r = 255; 
            $g = 255; 
            $b = 255;
        }
        $frontColor = imagecolorallocate($image, $r, $g, $b);
        
        imagefill ($image, 0, 0, $backColor);
        $valueLength = (function_exists('mb_strlen') ? mb_strlen($value) : strlen($value));
        for ($i = 0; $i < $valueLength; $i++) {
            $letter = (function_exists('mb_substr') ? mb_substr($value, $i, 1) : substr($value, $i, 1));
            $x = $this->configuration['width'] / ($valueLength + 1) * ($i + 0.5);
            $x = rand($x, $x + 4);
            $y = ($this->configuration['height'] + $fontSize) / 2;
            $angle = rand(-25, 25);
            imagettftext($image, $fontSize, $angle, $x, $y, $frontColor, ($this->configuration['font'] ? $this->configuration['font'] : __DIR__.DIRECTORY_SEPARATOR."cfont.ttf"), $letter);
        }
        if ($this->configuration['noise'] == true) {
            for ($i = 0; $i < 6; $i++) {
                $x1 = rand(0, $this->configuration['width'] - 1);
                $x = rand(0, $this->configuration['width'] - 1);
                $y1 = rand(0, $this->configuration['height'] - 1);
                $y = rand(0, $this->configuration['height'] - 1);
                imageline($image, $x1, $y1, $x, $y, $frontColor);
            }
        }
        
        ob_start();
        imagepng($image);
        $imageString = ob_get_contents();
        ob_end_clean();
        
        imagedestroy($image);
        
        return '<img src="data:image/png;base64,'.base64_encode($imageString).'" />';
    }

/**
 * Get javascript validation text for input
 * 
 * @param string $idPrefix Prefix for input id element
 * @return string Javascript code
 */    
    public function getJsValidation($idPrefix)
    {
        return 'if (!/^['.$this->configuration['letters'].']{'.$this->configuration['length'].'}$/i.test(form["captcha"].value)) {errors["'.$idPrefix.'captcha"] = '.json_encode($this->configuration['validateError']).';}'.self::JS_EOL;
    }
    
}
