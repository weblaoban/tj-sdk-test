<?php

namespace tj\sdk\test\models\utils;

use app\models\config\paygateConfig;
use app\models\config\merchantConfig;
use Yii;

class payUtil
{
    function generateDefPayParams($payData, $config)
    {
        $payData = $this->object_to_array($payData);
        $paramsMap = array();
        $paramsMap[AUTH_MERCHANT_ID] = (string)$config->id;
        $paramsMap[ORDER_ID] = $payData["orderId"];
        $paramsMap[ORDER_AMOUNT] = (double)$payData["amount"];
        $paramsMap[ORDER_CURRENCY] = $payData["currency"];
        $paramsMap[ORDER_TRANS_NAME] = $payData["transName"];
        $paramsMap[ORDER_DATE] = (string)$payData["date"];
        $paramsMap[PAY_OPTIONS_CLIENT_TYPE] = (string)$payData["clientType"];
        $paramsMap[PAY_OPTIONS_CLIENT_IP] = (string)$payData["clientIp"];
        if (array_key_exists('quantity',$payData)) {
            $paramsMap[ORDER_QUANTITY] = (string)$payData["quantity"];
        }
        if (array_key_exists('storeId',$payData)) {
            $paramsMap[ORDER_STORE_ID] = (string)$payData["storeId"];
        }
        if (array_key_exists('seller',$payData)) {
            $paramsMap[ORDER_SELLER] = (string)$payData["seller"];
        }
        if (array_key_exists('channelInfo',$payData) && strcmp($payData["channelInfo"], "") != 0) {
            $paramsMap[ORDER_CHANNEL_INFO] = $payData["channelInfo"];
        }
        if (array_key_exists('extendInfo',$payData) && strcmp($payData["extendInfo"], "") != 0) {
            $paramsMap[ORDER_EXTEND_INFO] = $payData["extendInfo"];
        }
        $paramsMap[PAY_OPTIONS_METHOD_ID] = (string)$payData["methodId"];

        $paramsMap[PAY_OPTIONS_TRADE_TYPE] = $payData["transType"];
        $paramsMap[PAY_OPTIONS_ASYNC_NOTICE_URL] = $payData["callbackUrl"];
        return $paramsMap;
    }

    function generateCreditCardPayParams($creditCard)
    {
        $creditCard=$this->object_to_array($creditCard);
        $paramsMap = array();
        $paramsMap[CREDIT_CARD_FIRST_NAME] = $creditCard["firstName"];
        $paramsMap[CREDIT_CARD_LAST_NAME] = $creditCard["lastName"];
        $paramsMap[CREDIT_CARD_CARD_NUM] = $creditCard["cardNum"];
        $paramsMap[CREDIT_CARD_CVV] = $creditCard["cvv"];
        $paramsMap[CREDIT_CARD_EXPIRE_MONTH] = $creditCard["expireMonth"];
        $paramsMap[CREDIT_CARD_EXPIRE_YEAR] = $creditCard["expireYear"];
        if(array_key_exists('addressCountry',$creditCard)){
            $paramsMap[CREDIT_CARD_ADDRESS_COUNTRY] = $creditCard["addressCountry"];
        }
        if(array_key_exists('phone',$creditCard)){
            $paramsMap[CREDIT_CARD_PHONE] = $creditCard["phone"];
        }
        if(array_key_exists('email',$creditCard)){
            $paramsMap[CREDIT_CARD_EMAIL] = $creditCard["email"];
        }
        return $paramsMap;
    }


function generateMerchantHostPayParams($params){
    $paramsMap = array();
$paramsMap[CREDIT_CARD_CARD_NUM] = $params->cardNum;
$paramsMap[CREDIT_CARD_PHONE] = $params->phone;
if($params->expireMonth){
    $paramsMap[CREDIT_CARD_EXPIRE_MONTH] = $params->expireMonth;
}
if($params->expireYear){
    $paramsMap[CREDIT_CARD_EXPIRE_YEAR] = $params->expireYear;
}
return $paramsMap;
}

function generateServerHostPayParams($params){
    $paramsMap = array();
        $paramsMap[CREDIT_CARD_CARD_NUM] = $params->cardNum;
        $paramsMap[CREDIT_CARD_PHONE] = $params->phone;
        $paramsMap[CREDIT_TOKEN] = $params->token;
    if($params->expireMonth){
        $paramsMap[CREDIT_CARD_EXPIRE_MONTH] = $params->expireMonth;
    }
    if($params->expireYear){
        $paramsMap[CREDIT_CARD_EXPIRE_YEAR] = $params->expireYear;
    }
        return $paramsMap;
    }


    function generateVerifyPhonePayParams($payData, merchantConfig $config) {
        $paramsMap = array();
        $paramsMap[AUTH_MERCHANT_ID] = $config->id;
        if($config->agentId){
            $paramsMap[AUTH_AGENT_ID] = $config->agentId;
        }
        $paramsMap[ORDER_ID] = $payData->orderId;
        $paramsMap[ORDER_TRANS_NAME] = $payData->transName;
        $paramsMap[ORDER_DATE] = $payData->date->getTime();
        $paramsMap[CREDIT_CARD_CARD_NUM] = $payData->cardNum;
        $paramsMap[CREDIT_CARD_PHONE] = $payData->phone;
        $paramsMap[PAY_OPTIONS_CLIENT_TYPE] = $payData->clientType;
        $paramsMap[PAY_OPTIONS_CLIENT_IP] = $payData->clientIp;
        if ($payData->channelInfo != null && $payData->channelInfo != "") {
            $paramsMap[ORDER_CHANNEL_INFO] = $payData->channelInfo;
        }
        if ($payData->extendInfo != null && $payData->extendInfo != "") {
            $paramsMap[ORDER_EXTEND_INFO] = $payData->extendInfo;
        }
        $paramsMap[PAY_OPTIONS_METHOD_ID] = $payData->methodId;
        $paramsMap[PAY_OPTIONS_TRADE_TYPE] = $payData->transType;
        $paramsMap[PAY_OPTIONS_ASYNC_NOTICE_URL] = $payData->callbackUrl;
        return $paramsMap;
    }
    function generateEnrollCardParams($params,merchantConfig $config) {
        $paramsMap = array();
        $paramsMap[AUTH_MERCHANT_ID] = $config->id;
        if($config->agentId){
            $paramsMap[AUTH_AGENT_ID] = $config->agentId;
        }
        $paramsMap[ORDER_ID] = $params->orderId;
        $paramsMap[PAY_OPTIONS_METHOD_ID] = $params->methodId;
        $paramsMap[ORDER_TRANS_NAME] = $params->transName;
        // $paramsMap[CODE_ORDER_ID] = $params->codeOrderId;
        $paramsMap['codeuqpayid'] = $params->codeOrderId;
        $paramsMap[ORDER_DATE] = $params->date->getTime();
        $paramsMap[CREDIT_CARD_CARD_NUM] = $params->cardNum;
        $paramsMap[CREDIT_CARD_VERIFY_CODE] = $params->verifyCode;
        $paramsMap[CREDIT_CARD_PHONE] = $params->phone;
        $paramsMap[PAY_OPTIONS_CLIENT_TYPE] = $params->clientType;
        $paramsMap[PAY_OPTIONS_CLIENT_IP] = $params->clientIp;
        if($params->cvv){
            $paramsMap[CREDIT_CARD_CVV] = $params->cvv;
        }
        if($params->expireMonth){
            $paramsMap[CREDIT_CARD_EXPIRE_MONTH] = $params->expireMonth;
        }
        if($params->expireYear){
            $paramsMap[CREDIT_CARD_EXPIRE_YEAR] = $params->expireYear;
        }
        if ($params->extendInfo != null && $params->extendInfo != "") {
            $paramsMap[ORDER_EXTEND_INFO] = $params->extendInfo;
        }
        $paramsMap[PAY_OPTIONS_TRADE_TYPE] = $params->transType;
        $paramsMap[PAY_OPTIONS_ASYNC_NOTICE_URL] = $params->callbackUrl;
        return $paramsMap;
    }
    function generateRefundParams($refund, $config)
    {
        $paramsMap = array();
        $paramsMap[AUTH_MERCHANT_ID] = (string)$config["Id"];
        $paramsMap[ORDER_ID] = $refund["orderId"];
        $paramsMap[ORDER_AMOUNT] = (string)$refund["amount"];
        $paramsMap[ORDER_DATE] = (string)$refund["date"];
        if ($refund["extendInfo"] != null && strcmp($refund["extendInfo"], "") != 0) {
            $paramsMap[ORDER_EXTEND_INFO] = $refund["extendInfo"];
        }
        $paramsMap[PAY_OPTIONS_TRADE_TYPE] = $refund["transType"];
        $paramsMap[PAY_OPTIONS_ASYNC_NOTICE_URL] = $refund["callbackUrl"];
        return $paramsMap;
    }

    function generateCancelParams($cancel, $config)
    {
        $paramsMap = array();
        $paramsMap[AUTH_MERCHANT_ID] = (string)$config["id"];
        $paramsMap[ORDER_ID] = $cancel["orderId"];
        $paramsMap[ORDER_DATE] = (string)$cancel["date"];
        if ($cancel["extendInfo"] != null && strcmp($cancel["extendInfo"], "") != 0) {
            $paramsMap[ORDER_EXTEND_INFO] = $cancel["extendInfo"];
        }
        $paramsMap[PAY_OPTIONS_TRADE_TYPE] = $cancel["transType"];
        return $paramsMap;

    }

    function generateQueryParams($query, $config)
    {
        $paramsMap = array();
        $paramsMap[AUTH_MERCHANT_ID] = (string)$config["id"];
        $paramsMap[ORDER_ID] = $query["orderId"];
        $paramsMap[ORDER_DATE] = (string)$query["date"];
        $paramsMap[PAY_OPTIONS_TRADE_TYPE] = $query["transType"];
        return $paramsMap;
    }

//throws UnsupportedEncodingException, UqpayRSAException
    function generateCashierLink($cashier, $config)
    {
        $getArray = (array)$cashier;
        $requestArr["merchantId"] = $getArray["merchantId"];
        $requestArr["transType"] = $getArray["transType"];
        $requestArr["date"] = $getArray["date"];
        $requestArr["orderId"] = $getArray["orderId"];
        if ($getArray["amount"] > 0) {
            $requestArr["amount"] = $getArray["amount"];
        }
        $requestArr["currency"] = $getArray["currency"];
        $requestArr["transName"] = $getArray["transName"];
        $requestArr["callbackUrl"] = $getArray["callbackUrl"];
        $requestArr["returnUrl"] = $getArray["returnUrl"];
        if ($getArray["quantity"] > 0) {
            $requestArr["quantity"] = $getArray["quantity"];
        }
        if ($getArray["storeId"] > 0) {
            $requestArr["storeId"] = $getArray["storeId"];
        }
        if ($getArray["seller"] > 0) {
            $requestArr["seller"] = (string)$getArray["seller"];
        }
        if ($getArray["channelInfo"] != null && strcmp($getArray["channelInfo"], "") !== 0) {
            $requestArr["channelInfo"] = $getArray["channelInfo"];
        }
        if ($getArray["extendInfo"] != null && strcmp($getArray["extendInfo"], "") !== 0) {
            $requestArr["extendInfo"] = $getArray["extendInfo"];
        }
        ksort($requestArr);
        $requestArr["sign"] = $this->signParams(http_build_query($requestArr), $config);
        ksort($requestArr);
        $urlQuery = http_build_query($requestArr);
        return $config->apiRoot . "?" . $urlQuery;
    }

    function signParams($data, paygateConfig $config)
    {
        $dirPath = Yii::$app->basePath;
        $prvPath = $config->getRSA()->privateKeyPath;
        $prvKey = file_get_contents($dirPath.'\\'.$prvPath);
        //转换为openssl密钥，必须是没有经过pkcs8转换的私钥
        $res = openssl_get_privatekey($prvKey);
        //调用openssl内置签名方法，生成签名$sign
        openssl_sign(urldecode(http_build_query($data)), $sign, $res);
        openssl_free_key($res);
        $sign = base64_encode($sign);
        $data['sign']=$sign;
        return $data;
    }
    function verifyUqpayNotice($paramsMap, paygateConfig $config)
    {
        if ($paramsMap[AUTH_SIGN] == null)
            Yii::warning("The payment result is not a valid uqpay result, sign data is missing");
        $needVerifyParams = array();
        foreach ($paramsMap as $k => $v) {
            if ($k != AUTH_SIGN) {
                $needVerifyParams[$k] = (string)$v;
            }
        }
        ksort($needVerifyParams);
        $paramsQuery = urldecode(http_build_query($needVerifyParams));
        $RSAUtil = new RSAUtil;
        $verify = $RSAUtil->verify($paramsQuery, (string)$paramsMap[AUTH_SIGN], $config->getRSA()->publicKeyPath->getPublicKey);
        if (!(boolean)$verify)  Yii::warning("The payment result is invalid, be sure is from the UQPAY server");
    }

    /**
     * @param $obj
     * @return array|void
     */
    function object_to_array($obj) {
        $obj = (array)$obj;
        foreach ($obj as $k => $v) {
            if (gettype($v) == 'resource') {
                return;
            }
            if (gettype($v) == 'object' || gettype($v) == 'array') {
                $obj[$k] = (array)object_to_array($v);
            }
        }

        return $obj;
    }
}
