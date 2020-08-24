<?php
/**
 * @package resizer
 * @version 2.2 beta
 * @author Antonio Correnti @ resetstudio.it
 */
namespace Bumip\Core;

function fastimagecopyresampled(&$dst_image, $src_image, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h, $quality = 3)
{
    // Plug-and-Play fastimagecopyresampled function replaces much slower imagecopyresampled.
    // Just include this function and change all "imagecopyresampled" references to "fastimagecopyresampled".
    // Typically from 30 to 60 times faster when reducing high resolution images down to thumbnail size using the default quality setting.
    // Author: Tim Eckel - Date: 09/07/07 - Version: 1.1 - Project: FreeRingers.net - Freely distributable - These comments must remain.
    //
    // Optional "quality" parameter (defaults is 3). Fractional values are allowed, for example 1.5. Must be greater than zero.
    // Between 0 and 1 = Fast, but mosaic results, closer to 0 increases the mosaic effect.
    // 1 = Up to 350 times faster. Poor results, looks very similar to imagecopyresized.
    // 2 = Up to 95 times faster.  Images appear a little sharp, some prefer this over a quality of 3.
    // 3 = Up to 60 times faster.  Will give high quality smooth results very close to imagecopyresampled, just faster.
    // 4 = Up to 25 times faster.  Almost identical to imagecopyresampled for most images.
    // 5 = No speedup. Just uses imagecopyresampled, no advantage over imagecopyresampled.

    if (empty($src_image) || empty($dst_image) || $quality <= 0) {
        return false;
    }
    if ($quality < 5 && (($dst_w * $quality) < $src_w || ($dst_h * $quality) < $src_h)) {
        $temp = imagecreatetruecolor($dst_w * $quality + 1, $dst_h * $quality + 1);
        imagecopyresized($temp, $src_image, 0, 0, $src_x, $src_y, $dst_w * $quality + 1, $dst_h * $quality + 1, $src_w, $src_h);
        imagecopyresampled($dst_image, $temp, $dst_x, $dst_y, 0, 0, $dst_w, $dst_h, $dst_w * $quality, $dst_h * $quality);
        imagedestroy($temp);
    } else {
        imagecopyresampled($dst_image, $src_image, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h);
    }
    return true;
}
class Resizer
{
    public $original;
    public $resized;
    public function resizer($src, $width = false, $height = false, $imgname = false, $cachetime = 3600, $cachefolder = false)
    {
        ini_set("memory_limit", "128M");
        /**
         *  If width or height are false "x" will be assigned
         */
        if (!$width) {
            $w = "x";
        } else {
            $w = $width;
        }
        if (!$height) {
            $h = "x";
        } else {
            $h = $height;
        }
        /**
         * Setup cachefolder
         */
        $no_output = false;
        if ($cachefolder == 'no-output') {
            $no_output = true;
            $cachefolder = false;
        }
        if (!$cachefolder) {
            if (strpos(dirname($src), "http://") === 0) {
                $cachefolder = "content/uploaded/cache";
            } else {
                $cachefolder = dirname($src)."/cache/";
            }
        }

        /*
         * Setup path to cached picture
         */
        if ($imgname) {
            $cached = $cachefolder."{$w}_{$h}_".basename($imgname);
        } else {
            $cached = null;
        }
        if ($imgname and file_exists($cached)) {
            if (file_exists($cached)) {
                $mime = pathinfo(basename($imgname));
                $mime = strtolower($mime["extension"]);
                if ($mime == "jpg") {
                    $mime = "jpeg";
                }
                header("Content-type: image/".$mime);
                echo file_get_contents($cached);
                if (filemtime($cached)+$cachetime < time()) {
                    unlink($cached);
                }
            }
        } else {
            $this->img = new imageR($src);
            $this->img->resize($width, $height);
            if (!$no_output) {
                $this->img->output($cached);
            } else {
                $this->img->save($cached);
            }
        }
    }
}
class imageR
{
    public $src;
    public $mime;
    public $img;
    public $createfunction;
    public $w;
    public $h;
    public $x;
    public $y;
    public $frame_w = false;
    public $frame_h = false;
    public $dst_w;
    public $dst_h;
    public $dst_x;
    public $dst_y;
    public $m_ratio;
    public $resampled = false;
    /**
     *
     * @param <type> $src
     * @todo controllare se non è immagine
     */
    public function imageR($src = false)
    {
        if ($src) {
            $this->src = $src;
            $this->get_file_mime();
            $this->create_image();
        }
    }
    public function resize($dst_w = false, $dst_h = false)
    {
        $this->dst_x = 0;
        $this->dst_y = 0;
        if ($dst_w == 'ratio') {
            $dst_w = round($this->w / $dst_h);
            $dst_h = false;
        }
        if (!$dst_w and !$dst_h) {
            return false;
        }
        if (!$dst_w) {
            $dst_w = round($dst_h*$this->aspect_ratio);
        }
        if (!$dst_h) {
            $dst_h = round($dst_w/$this->aspect_ratio);
        }
        $ratio = $dst_w/$dst_h;
        // If is more take the width
        if ($ratio > $this->aspect_ratio) {
            $this->dst_w = $dst_w;
            $this->dst_h = round($dst_w/$this->aspect_ratio);
            $this->crop($dst_w, $dst_h, 0, round(($this->dst_h-$dst_h)/2));
        } else {
            $this->dst_h = $dst_h;
            $this->dst_w = round($dst_h*$this->aspect_ratio);
            $this->crop($dst_w, $dst_h, round(($this->dst_w-$dst_w)/2), 0);
        }
//        if($dst_w AND $dst_h){
//            //dst_lato_lungo come img_lato_corto
//            $dst_x = 0;
//            $dst_y = 0;
//            $quad = false;
//            $wide = false;
//            if($dst_w > $dst_h){
//                $wide = true;
//            }else if($dst_w == $dst_h){
//                $quad = true;
//            }
//            $dst_long = max($dst_w, $dst_h);
//            if(($this->aspect != "wide") AND ($wide OR $quad)
//                OR ($this->aspect == "wide" AND $wide)
//            ){
//                //lato lungo larghezza
//                $this->resize($dst_long);
//            }else{
//                //lato lungo altezza
//                $this->resize(false, $dst_long);
//            }
//            if($dst_w > $dst_h OR ($this->aspect != "wide" AND($quad))){
//                $dst_y = round(($this->dst_h-$dst_h)/2);
//            }else{
//                $dst_x = round(($this->dst_w-$dst_w)/2);
//            }
//            $this->crop($dst_w, $dst_h, $dst_x, $dst_y);
//        }else{
//            if(!$dst_h AND $dst_w){
//                $this->m_ratio = $this->w/$dst_w;
//                $dst_h = round($this->h/$this->m_ratio);
//            }else if(!$dst_w AND $dst_h){
//                $this->m_ratio = $this->h/$dst_h;
//                $dst_w = round($this->w/$this->m_ratio);
//            }
//            $this->dst_h = $dst_h;
//            $this->dst_w = $dst_w;
//        }
    }
    public function create_image()
    {
        ini_set('memory_limit', '320M');
        $this->createfunction = $fun = "imagecreatefrom".$this->mime;
        $this->img = $fun($this->src);
        $this->get_size();
        $this->get_aspect();
    }
    public function get_size()
    {
        $this->w = imagesx($this->img);
        $this->h = imagesy($this->img);
    }
    public function get_file_mime($src = false)
    {
        if (!$src) {
            $src = $this->src;
        }
        $this->mime = pathinfo(basename($src));
        $this->mime = strtolower($this->mime["extension"]);
        if ($this->mime == "jpg") {
            $this->mime = "jpeg";
        }
        return $this->mime;
    }
    public function setup()
    {
    }
    public function cutCoords()
    {
    }
    public function get_aspect()
    {
        $this->aspect_ratio = $this->w/$this->h;
        if ($this->aspect_ratio < 1) {
            return $this->aspect = "high";
        } else {
            return $this->aspect = "wide";
        }
    }
    public function crop($frame_w, $frame_h, $dst_x, $dst_y)
    {
        $this->frame_w = $frame_w;
        $this->frame_h = $frame_h;
        $this->dst_x = -($dst_x);
        $this->dst_y = -($dst_y);
    }
    public function resample()
    {
        if (!$this->frame_w) {
            $this->frame_w = $this->dst_w;
            $this->frame_h = $this->dst_h;
        } elseif (!$this->dst_w) {
            $this->dst_w = $this->w;
            $this->dst_h = $this->h;
        }
        $this->dst_img = imagecreatetruecolor($this->frame_w, $this->frame_h);
        if ($this->mime == "png" or $this->mime == "gif") {
            imagesavealpha($this->dst_img, 1);
            imagealphablending($this->dst_img, false);
            imagecopyresampled($this->dst_img, $this->img, $this->dst_x, $this->dst_y, $this->x, $this->y, $this->dst_w, $this->dst_h, $this->w, $this->h);
        } else {
            fastimagecopyresampled($this->dst_img, $this->img, $this->dst_x, $this->dst_y, $this->x, $this->y, $this->dst_w, $this->dst_h, $this->w, $this->h);
        }
        $this->resampled = true;
        //print_r($this);
    }
    public function save($save = null, $compr = 'standard')
    {
        if (!$this->resampled) {
            $this->resample();
        }
        $m = $this->mime;
        if ($compr == "standard") {
            if ($m == "png") {
                $compr = 9;
            } else {
                $compr = 80;
            }
        }
        $fun = "image".ucfirst($m);
        $dir = str_replace(basename($save), "", $save);
        if ($save != null) {
            if (!file_exists(dirname($save))) {
                mkdir(dirname($save));
            }
        }
        if ($m == "jpeg") {
            $fun($this->dst_img, $save, $compr);
        } else {
            $fun($this->dst_img, $save);
        }
        return true;
    }
    public function output($save = null, $compr = "standard")
    {
        header("Content-type: image/".$this->mime);
        $this->save($save, $compr);
        echo file_get_contents($save);
        return true;
        //print_r($this);

        //header("Content-type: image/".$this->mime);
        //imageJpeg($this->dst_img);
    }
}
