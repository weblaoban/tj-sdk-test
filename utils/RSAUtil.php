<?php
namespace tj\sdk\test\utils;
use Yii;
class RSAUtil {

//throws UqpayRSAException
    function verify($data,$sign,$publicKey) {
        $res = openssl_get_publickey($publicKey);
        //调用openssl内置方法验签，返回bool值
        $result = (bool)openssl_verify($data, base64_decode($sign), $res);
        //释放资源
        openssl_free_key($res);
        //返回资源是否成功
        return $result;
    }
}
