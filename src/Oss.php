<?php
namespace xjryanse\logic;

use OSS\OssClient;
use OSS\Core\OssException;
/**
 * 一维数组处理逻辑
 */
class Oss
{
    use \xjryanse\traits\InstTrait;
    
    protected $bucket;

    protected $ossClient;
    /**
     * 重写实例化方法
     * @param type $uuid
     */
    protected function __construct( $uuid = 0 ){
        $this->uuid      = $uuid;

        $config             = config('xiesemi.oss');
        $accessKeyId        = Arrays::value($config, 'accessKeyId');
        $accessKeySecret    = Arrays::value($config, 'accessKeySecret');
        $endpoint           = Arrays::value($config, 'endpoint');
        $this->ossClient = new OssClient($accessKeyId, $accessKeySecret, $endpoint);
        $this->bucket = Arrays::value($config, 'bucket');
    }
    /**
     * 上传文件
     * @param type $bucket  
     * @param type $object
     * @param type $file
     */
    public function uploadFile($fileName, $file){
        return $this->ossClient->uploadFile($this->bucket,$fileName,$file);
    }
    
    public function getObject($fileName){
        return $this->ossClient->getObject($this->bucket,$fileName);
    }
    
    public function doesBucketExist($bucketName){
        return $this->ossClient->doesBucketExist($bucketName);
    }

    public function listBuckets(){
        return $this->ossClient->listBuckets();
    }
    
    public function signUrl($fileName){
        return $this->ossClient->signUrl($this->bucket,$fileName);
    }

}
