<?php

namespace Lyx\Graphics;

// Gradient class imports

use Lyx\Graphics\Color;
use function Lyx\Graphics\Core\imagecopymerge_alpha;

class Image
{
  public $src,
    $image_type,
    $mime,
    $bits,
    $width,
    $height,
    $antialias,
    $transparent;

  public $gis;

  public $im,
    $isImage,
    $isImageData;

  public function __construct()
  {
    $ac = func_num_args();
    $av = func_get_args();

    if($ac == 1)
      $this->openImage($av[0]);
    elseif($ac == 2)
      $this->createImage($av[0], $av[1]);
    elseif($ac == 3)
      $this->createImage($av[0], $av[1], $av[2]);
    else
      $this->resetData();
  }

  public function setImage($src)
  {
    $this->resetData();

    $this->gis = getimagesize($src);
    if($this->gis !== FALSE) {
      $this->src = $src;
      list($this->width, $this->height, $this->image_type, , $this->bits, $this->mime) = $this->gis;
      $this->isImage = TRUE;
    }
    
    return $this->isImage;
  }

  public function openImage()
  {
    $ret = FALSE;
    if(func_num_args() >= 1)
      $this->setImage(func_get_arg(0));

    if($this->isImage && !empty($this->src)) {
      $this->resetData();
        
      switch($this->image_type) {
        case ImageTypes::PNG:
          $this->im = imagecreatefrompng($this->src);
          break;
        case ImageTypes::JPEG:
          $this->im = imagecreatefromjpeg($this->src);
          break;
        case ImageTypes::GIF:
          $this->im = imagecreatefromgif($this->src);
          break;
        case ImageTypes::WBMP:
          $this->im = imagecreatefromwbmp($this->src);
          break;
        case ImageTypes::XBM:
          $this->im = imagecreatefromxbm($this->src);
          break;
      }

      $ret = $this->hasImageData();
    }
    
    return $ret;
  }

  public function fromResource($resource)
  {
    $this->resetData();
    $this->im = $resource;
  }

  public function hasImageData()
  {
    return $this->im !== FALSE;
  }

  public function getWidth()
  {
    if($this->hasImageData())
      return imagesx($this->im);
    else
      return $this->width;
  }

  public function getHeight()
  {
    if($this->hasImageData())
      return imagesy($this->im);
    else
      return $this->height;
  }

  public function createImage($width, $height, $transparent = TRUE)
  {
    $this->resetData();
    $this->im = imagecreateTRUEcolor($width, $height);
    
    if($this->im !== FALSE) {
      $this->width = imagesx($this->im);
      $this->height = imagesy($this->im);
      
      if($transparent) {
        $this->setTransparent();
        $this->fillTransparent();
      }
    }
    
    return $this->im !== FALSE;
  }

  public function fullFill($r, $g = 0, $b = 0)
  {
    if(is_object($r) || is_array($r))
      list($r, $g, $b) = array_values(Color::colorToRgb($r));
    
    $colfill = imagecolorallocate($this->im, $r, $g, $b);
    imagefill($this->im, 0, 0, $colfill);
    imagecolordeallocate($this->im, $colfill);
  }

  public function setTransparent($value = TRUE)
  {
    $this->transparent = $value;
    imagealphablending($this->im, !$value);
    imagesavealpha($this->im, $value);
  }

  public function getTransparent()
  {
    return $this->transparent;
  }

  public function setAntialiasing($value = TRUE)
  {
    $this->antialias = $value;
    imageantialias($this->im, $value);
  }

  public function getAntialiasing()
  {
    return $this->antialias;
  }

  public function fillTransparent($r = Color::RGB_MAX, $g = Color::RGB_MAX, $b = Color::RGB_MAX)
  {
    $trans = imagecolorallocatealpha($this->im, $r, $g, $b, Color::PHP_TRANSPARENT);
    imagefilledrectangle($this->im, 0, 0, imagesx($this->im), imagesy($this->im), $trans);
  }

  public function resetData()
  {
    if($this->im !== NULL)
      imagedestroy($this->im);

    $this->src = '';
    $this->image_type = 0;
    $this->mime = '';
    $this->bits = 0;
    $this->width = 0;
    $this->height = 0;
    $this->antialias = FALSE;
    $this->transparent = FALSE;
    $this->gis = FALSE;
    $this->im = NULL;
    $this->isImage = FALSE;
    $this->isImageData = FALSE;
  }

  public function clear()
  {
    $this->resetData();
  }

  public function bitCopyMergeAlpha($src_im, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h, $pct = 100)
  {
    $sim = $this->this_or_image($src_im);
    if($sim)
      return imagecopymerge_alpha($this->im, $sim, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h, $pct);
    else
      return FALSE;
  }

  public function bitCopyMerge($src_im, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h, $pct = 100)
  {
    $sim = $this->this_or_image($src_im);
    if($sim)
      return imagecopymerge($this->im, $sim, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h, $pct);
    else
      return FALSE;
  }

  public function bitCopy($src_im, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h)
  {
    $sim = $this->this_or_image($src_im);
    
    if($sim)
      return imagecopy($this->im, $sim, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h);
    else
      return FALSE;
  }

  public function writeFile($file, $type = NULL, $a1 = NULL, $a2 = NULL)
  {
    $this->write_image($file, $type, $a1, $a2);
  }

  public function write($type = NULL, $a1 = NULL, $a2 = NULL)
  {
    $this->write_image(NULL, $type, $a1, $a2);
  }

  private function write_image($file = NULL, $type = NULL, $a1 = NULL, $a2 = NULL)
  {
    $ret = FALSE;

    if($type === NULL)
      $type = $this->image_type;
    
    if(empty($type))
      $type = ImageTypes::PNG;
    
    if($this->hasImageData()) {
      switch($type) {
        case ImageTypes::PNG:
          if($file !== NULL)
            $ret = imagepng($this->im, $file);
          else
            $ret = imagepng($this->im);
          break;
        case ImageTypes::JPEG:
          if($a1 !== NULL)
            $ret = imagejpeg($this->im, $file, $a1);
          else
            $ret = imagejpeg($this->im, $file);
          break;
        case ImageTypes::GIF:
          $ret = imagegif($this->im, $file);
          break;
        case ImageTypes::WBMP:
          if($a1 !== NULL)
            $ret = imagewbmp($this->im, $file, $a1);
          else
            $ret = imagewbmp($this->im, $file);
          break;
        case ImageTypes::XBM:
          if($a1 !== NULL)
            $ret = imagexbm($this->im, $file, $a1);
          else
            $ret = imagexbm($this->im, $file);
          break;
      }
    }
  }

  private function this_or_image($im)
  {
    $rim = NULL;
    if(is_object($im)) {
      if(get_class($im) == __CLASS__)
        $rim = $im->im;
    }
    else
      $rim = $im;
    
    return $rim;
  }
}


// ImageTypes class

class ImageTypes
{
  const GIF		= IMAGETYPE_GIF,
    JPEG		= IMAGETYPE_JPEG,
    PNG		= IMAGETYPE_PNG,
    SWF		= IMAGETYPE_SWF,
    PSD		= IMAGETYPE_PSD,
    BMP		= IMAGETYPE_BMP,
    TIFF_II	= IMAGETYPE_TIFF_II,
    TIFF_MM	= IMAGETYPE_TIFF_MM,
    JPC		= IMAGETYPE_JPC,
    JP2		= IMAGETYPE_JP2,
    JPX		= IMAGETYPE_JPX,
    JB2		= IMAGETYPE_JB2,
    SWC		= IMAGETYPE_SWC,
    IFF		= IMAGETYPE_IFF,
    WBMP		= IMAGETYPE_WBMP,
    XBM		= IMAGETYPE_XBM,
    ICO		= IMAGETYPE_ICO;

  public static function typeToMimeType($type)
  {
    return image_type_to_mime_type($type);
  }
}
