<?php

namespace xjryanse\logic;

use xjryanse\logic\Arrays;
use PhpOffice\PhpWord\PhpWord;
use Endroid\QrCode\QrCode;
use PhpOffice\PhpWord\SimpleType\Jc;
use xjryanse\curl\Query;
/**
 * 压缩文件处理
 */
class Word {

    protected $phpWord;
    protected $templateProcessor;
    /**
     * 实例
     */
    public function __construct() {
        $this->phpWord = new PhpWord();
    }
    /**
     * 加载目标
     * @param type $templatePath
     */
    public function loadTemplate($templatePath){
        $this->templateProcessor  = $this->phpWord->loadTemplate($templatePath);
    }

    /**
     * 根据表格行列数，复制行
     */
    public function cloneRow($mark, $allCount, $col = 3){
        $line = ceil($allCount / $col);
        if($line > 1){
            $this->templateProcessor->cloneRow($mark, $line);
        }
    }
    
    /**
     * 数组初始化
     * @param type $array       数组
     * @param type $keyReflect  键值对映射
     * @param type $col         列数
     */
    public function setDataArr($arr, $keyReflect, $col = 3){
        // 延伸到指定长度
        $array = self::arrExt($arr, $col);

        foreach($array as $index=>$v){
            foreach($keyReflect as $key=>$conf){
                //TODO配置
                $mark = $conf;
                // 取标签名称
                $markN = self::markN($mark, count($array), $index, $col);
                // 写入文本
                $this->templateProcessor->setValue($markN, Arrays::value($v,$key));
            }
        }
        // 设置空
    }
    /**
     * 设置二维码
     * @param type $arr       数组
     * @param type $keyReflect  映射
     * @param type $col         列数
     */
    public function setQrcodeArr($arr, $keyReflect, $col = 3){
        // 延伸到指定长度
        $array = self::arrExt($arr, $col);

        foreach($array as $index=>$v){
            foreach($keyReflect as $key=>$conf){
                //TODO配置
                $mark = $conf;
                // 取标签名称
                $markN = self::markN($mark, count($array), $index, $col);
                if(Arrays::value($v,$key)){
                    $this->putQrCode( $markN, Arrays::value($v,$key));
                } else {
                    //写一个空的就行
                    $this->templateProcessor->setValue($markN, Arrays::value($v,$key));
                }
            }
        }
    }
    
    /**
     * 数组延申到适配指定列数（避免空标签存在word中）
     * @param type $array
     * @param type $col
     */
    private static function arrExt($array, $col){
        $mode = count($array) % $col;
        if(!$mode){
            return $array;
        }
        for($i = 0;$i<$col-$mode;$i++){
            $array[] = [];
        }
        return $array;
    }
    
    /**
     * 生成标签名称  ${name1#1}
     * @param type $mark        标签名
     * @param type $allCount    总数据数
     * @param type $index       数据索引
     * @param type $col         列数
     */
    private static function markN($mark, $allCount, $index, $col){
        // 取模
        $line = intval($index / $col) + 1;
        $mode = $index % $col + 1;
        // 添加了列 ${name1#1}
        $markN = $mark . $mode;
        if($allCount > $col){
            $markN .= '#'.$line;
        }
        return $markN;
    }
    /**
     * 保存
     * @param type $filename
     * @return type
     */
    public function save($filename = ''){
        return $this->templateProcessor->saveAs($filename);  
    }
    /**
     * 写入二维码
     * @param type $mark    标签
     * @param type $text    二维码文本
     * @param type $conf    配置数组
     */
    private function putQrCode($mark, $text, $conf = []){

        // 二维码尺寸
        $cSize      = Arrays::value($conf, 'size') ? : 300;
        $cWidth     = Arrays::value($conf, 'width') ? : 100;
        $cHeight    = Arrays::value($conf, 'height') ? : 100;

        // 临时存储路径
        $qrCodeFilePath = Arrays::value($conf, 'tmpQrPath') ? : './Uploads/Download/CanDelete/qr'.md5($text).'.png';
        
        // $qrCode = new QrCode('https://www.example.com');
        $qrCode = new QrCode($text);

        $qrCode->setSize($cSize);
        $qrCode->writeFile($qrCodeFilePath);

        $this->templateProcessor->setImageValue($mark, array(
            'path'      => $qrCodeFilePath,
            'width'     => $cWidth,
            'height'    => $cHeight,
            'alignment' => Jc::CENTER,
        ));
        // 删除临时二维码图片
        unlink($qrCodeFilePath);
    }
    /**
     * 页面数
     * 20231220
     * @param type $urlFull
     * @return type
     */
    public static function pageCount($urlFull){
        //TODO,linux下如何自行处理？？
        $wordInfoUrl = "https://office.xiesemi.cn/index.php/word/getDocInfo";
        $url = urlencode($urlFull);
        $infoUrl    = $wordInfoUrl . '?filePath=' . $url;
        $infores    = Query::geturl($infoUrl);
        $info       = $infores['data'];
        return $info ? Arrays::value($info, 'pageCount') : 0;
    } 

}
