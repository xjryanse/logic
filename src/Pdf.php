<?php

namespace xjryanse\logic;

use think\Container;

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
    public static function toPngMany($pdf, $path = "") {
        $im = new \Imagick();
        $im->setResolution(120, 120); //设置分辨率 值越大分辨率越高
        $im->setCompressionQuality(100);
        $im->readImage($pdf);
        Debug::debug('$pdf', $pdf);
        Debug::debug('$im', $im);
        foreach ($im as $k => $v) {
            $v->setImageFormat('png');
            $v->setImageBackgroundColor('#ffffff');
            Debug::debug('$v', $v);
            //20220119增
            $v->setImageAlphaChannel(\Imagick::ALPHACHANNEL_REMOVE);
            $v->mergeImageLayers(\Imagick::LAYERMETHOD_FLATTEN);
            //20220119删
            //$v = $v->flattenImages();
            if (!$path) {
                $path = "images/" . date('Ymd') . '/';
            }
            //20220811:增加创建文件夹
            $basePath = Container::get('app')->getRootPath();
            $pathName = $basePath . 'public' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . date('Ymd') . DIRECTORY_SEPARATOR;
            if (!is_dir($pathName)) {
                mkdir($pathName, 0777, true);
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
    public static function toPngOne($pdf, $path = "") {
        try {
            $im = new \Imagick();
            $im->setCompressionQuality(100);
            $im->setResolution(120, 120); //设置分辨率 值越大分辨率越高
            $im->readImage($pdf);

            $canvas = new \Imagick();
            $imgNum = $im->getNumberImages();
            //$canvas->setResolution(120, 120);
            foreach ($im as $k => $sub) {
                $sub->setImageFormat('png');
                //$sub->setResolution(120, 120);
                $sub->stripImage();
                $sub->trimImage(0);
                $width = $sub->getImageWidth() + 10;
                $height = $sub->getImageHeight() + 10;
                if ($k + 1 == $imgNum) {
                    $height += 10;
                } //最后添加10的height
                $canvas->newImage($width, $height, new \ImagickPixel('white'));
                $canvas->compositeImage($sub, \Imagick::COMPOSITE_DEFAULT, 5, 5);
            }

            $canvas->resetIterator();
            if (!$path) {
                $path = "images/" . date('Ymd') . '/';
            }
            $canvas->appendImages(true)->writeImage($path . microtime(true) . '.png');
        } catch (Exception $e) {
            throw $e;
        }
    }

    /*
     * 20231220页数
     */

    public static function pageCount($pdf) {
        $im = new \Imagick();
        //$im->setResolution(120, 120); //设置分辨率 值越大分辨率越高
        //$im->setCompressionQuality(100);
        $im->readImage($pdf);
        return count($im);
    }
}
