<?php

namespace xjryanse\logic;

/**
 * Pdf处理
 */
class Pdf {
    /**
     * pdf转化为多张png
     * @param type $pdf
     * @param type $path
     * @return boolean|string
     */
    public static function pdfToPngMany($pdf, $path="")
    {
        $im = new \Imagick();
        $im->setResolution(120, 120); //设置分辨率 值越大分辨率越高
        $im->setCompressionQuality(100);
        $im->readImage($pdf);
        foreach ($im as $k => $v) {
            $v->setImageFormat('png');
            $v->setImageBackgroundColor('#ffffff');
            $v = $v->flattenImages();
            if(!$path){
                $path = "images/".date('Ymd').'/';
            }
            $fileName = $path . md5($k . time()) . '.png';
            if ($v->writeImage($fileName) == true) {
                $return[] = $fileName;
            }
        }
        return $return;
    }
    /**
     * pdf转化为单张png
     * @param type $pdf
     * @param type $path
     * @throws \xjryanse\logic\Exception
     */
    public static function pdfToPngOne($pdf, $path=""){
        try {
            $im = new \Imagick();
            $im->setCompressionQuality(100);
            $im->setResolution(120, 120);//设置分辨率 值越大分辨率越高
            $im->readImage($pdf);

            $canvas = new \Imagick();
            $imgNum = $im->getNumberImages();
            //$canvas->setResolution(120, 120);
            foreach ($im as $k => $sub) {
                $sub->setImageFormat('png');
                //$sub->setResolution(120, 120);
                $sub->stripImage();
                $sub->trimImage(0);
                $width  = $sub->getImageWidth() + 10;
                $height = $sub->getImageHeight() + 10;
                if ($k + 1 == $imgNum) {
                    $height += 10;
                } //最后添加10的height
                $canvas->newImage($width, $height, new \ImagickPixel('white'));
                $canvas->compositeImage($sub, \Imagick::COMPOSITE_DEFAULT, 5, 5);
            }

            $canvas->resetIterator();
            if(!$path){
                $path = "images/".date('Ymd').'/';
            }
            $canvas->appendImages(true)->writeImage($path . microtime(true) . '.png');
        } catch (Exception $e) {
            throw $e;
        }
    }

}
