<?php
namespace xjryanse\logic;

/**
 * 一维数组处理逻辑
 */
class Rsa
{
    public static $rsa_public_key  = "";
    public static $rsa_private_key = "";

    public static function setPublicKey($rsa_public_key)
    {
        if (\strpos($rsa_public_key, "\n") == false) {
            $rsa_public_key = "-----BEGIN PUBLIC KEY-----\n" . wordwrap($rsa_public_key, 64, "\n", true) . "\n-----END PUBLIC KEY-----";
        }
        self::$rsa_public_key = openssl_pkey_get_public($rsa_public_key);
        var_dump($rsa_public_key, self::$rsa_public_key);
    }

    // 加密字符长度小于117
    public static function encrypt($decrypted, $base64 = false)
    {
        openssl_public_encrypt($decrypted, $encrypted, self::$rsa_public_key, OPENSSL_PKCS1_PADDING);
        if ($base64) {
            return base64_encode($encrypted);
        } else {
            return bin2hex($encrypted);
        }
    }

    public static function setPrivateKey($rsa_private_key)
    {
        if (\strpos($rsa_private_key, "\n") == false) {
            $rsa_private_key = "-----BEGIN PRIVATE KEY-----\n" . wordwrap($rsa_private_key, 64, "\n", true) . "\n-----END PRIVATE KEY-----";
        }
        self::$rsa_private_key = openssl_pkey_get_private($rsa_private_key);
    }

    public static function decrypt($encrypted, $base64 = false)
    {
        if ($base64) {
            $encrypted = base64_decode($encrypted);
        } else {
            $encrypted = hex2bin($encrypted);
        }
        $chunks = str_split($encrypted, 2048 / 8);
        foreach ($chunks as $in) {
            openssl_private_decrypt($in, $out, self::$rsa_private_key, OPENSSL_PKCS1_PADDING);
            $decrypted .= $out;
        }
        return $decrypted;
    }
    
    
    /**
     * @description 创建RSA 公钥私钥
     * @return array|bool
     */
    public static function createRsaKey(){
        //配置信息
        $config = array(
            'config'            => 'D:\phpstudy_pro\Extensions\php\php8.0.2nts\extras\ssl\openssl.cnf',//找到你的PHP目录下openssl配置文件
            'digest_alg'        => 'sha512',
            'private_key_bits'  => 1024,//指定多少位来生成私钥
            'private_key_type'  => OPENSSL_KEYTYPE_RSA
        );
        $res = openssl_pkey_new($config);
        //获取私钥
        openssl_pkey_export($res, $privateKey, null, $config);
        //获取公钥
        $details = openssl_pkey_get_details($res);
        $publicKey = $details['key'];

        return array('public_key' => $publicKey, 'private_key' => $privateKey);
    }

}
