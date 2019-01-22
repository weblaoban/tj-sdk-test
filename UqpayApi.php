<?php

namespace app;

use app\models\common\BankCardDTO;
use app\models\common\BankCardExtendDTO;
use app\models\common\BaseJsonRequestDTO;
use app\models\common\MerchantHostDTO;
use app\models\common\PayOptionsDTO;
use app\models\common\ServerHostDTO;
use app\models\config\AppgateConfig;
use app\models\config\cashierConfig;
use app\models\config\merchantConfig;
use app\models\config\paygateConfig;
use app\models\emvco\EmvcoCreateDTO;
use app\models\emvco\EmvcoGetPayloadDTO;
use app\models\enroll\EnrollOrder;
use app\models\enroll\VerifyOrder;
use app\models\exchangeRate\ExchangeRateQueryDTO;
use app\models\merchant\MerchantRegisterDTO;
use app\models\pay\PayOrder;
use app\models\preAuth\PreAuthOrder;
use app\models\result\TransResult;
use app\utils\httpRequest;
use app\utils\payMethod;
use app\utils\payUtil;
use app\models\common\AuthDTO;
use Yii;

include 'utils/constants.php';

class UqpayApi
{
    private $paygateConfig;
    private $merchantConfig;
    private $cashierConfig;
    private $appgateConfig;
    private $auth;

    public function __construct(paygateConfig $paygateConfig, merchantConfig $merchantConfig, cashierConfig $cashierConfig, AppgateConfig $appgateConfig)
    {
        $this->paygateConfig = $paygateConfig;
        $this->merchantConfig = $merchantConfig;
        $this->cashierConfig = $cashierConfig;
        $this->appgateConfig = $appgateConfig;
        $this->auth = new authDTO();
        $this->auth->agentId = $merchantConfig->agentId;
        $this->auth->merchantId = $merchantConfig->id;
    }


    public function updateMerchantId($merchantId)
    {
        $this->auth->merchantId = $merchantId;
    }

    public function paygateApiUrl($url)
    {
        return $this->paygateConfig->apiRoot . $url;
    }

    public function appgateApiUrl($url)
    {
        return $this->appgateConfig->apiRoot . $url;
    }

    private function setAuthForJsonParams(BaseJsonRequestDTO $jsonParams)
    {
        $jsonParams->merchantId = $this->auth->merchantId;
        $jsonParams->agentId = $this->auth->agentId;
    }


    private function generatePayParamsMap($payData, $scenes)
    {
        $payMethod = new payMethod();
        $payUtil = new payUtil();
        $payData["signType"] = $payMethod->SignTypeEnum['RSA'];

        $paramsMap = $payUtil->generateDefPayParams($payData, $this->merchantConfig);
        switch ($scenes) {
            case "QRCode":
                $paramsMap[PAY_OPTIONS_SCAN_TYPE] = $payMethod->UqpayScanType[$payData['scanType']];
                if ($payData["scanType"] === 0) {
                    $paramsMap["identity"] = $payData["identity"];
                }
                break;
            case "OfflineQRCode":
                $paramsMap[PAY_OPTIONS_SCAN_TYPE] = $payMethod->UqpayScanType[$payData->scanType];
                $paramsMap[PAY_OPTIONS_MERCHANT_CITY] = $payData['merchantCity'];
                $paramsMap[PAY_OPTIONS_TERMINALID_ID] = $payData['terminalId'];
                break;
            case "OnlinePay":
                $paramsMap[PAY_OPTIONS_SYNC_NOTICE_URL] = $payData['returnUrl'];
                break;
            case "InApp":
                $paramsMap[PAY_OPTIONS_SYNC_NOTICE_URL] = $payData['returnUrl'];
                break;
            case "CreditCard":
            case "ThreeDCreditCard":
                $creditCardParams = $payUtil->generateCreditCardPayParams($payData->bankCard);
                $paramsMap = array_merge($creditCardParams, $paramsMap);
                break;
            case "MerchantHost":
                $merchantHostParams = $payUtil->generateMerchantHostPayParams($payData->merchantHost);
                $paramsMap = array_merge($merchantHostParams, $paramsMap);
                break;
            case "ServerHost":
                $serverHostParams = $payUtil->generateServerHostPayParams($payData->serverHost);
                $paramsMap = array_merge($serverHostParams, $paramsMap);
                break;
        }
        ksort($paramsMap);
        return $payUtil->signParams($paramsMap, $this->paygateConfig);
    }


    private function directFormPost($url, $paramsMap)
    {
        $payUtil = new payUtil();
        $httpRequest = new httpRequest();
        $resultMap = $httpRequest->httpArrayPost($url, $paramsMap);
        $payUtil->verifyUqpayNotice($resultMap, $this->paygateConfig);
        return $resultMap;
    }

    private function directJsonPost($url, $paramsMap)
    {
        $payUtil = new payUtil();
        $httpRequest = new httpRequest();
        ksort($paramsMap);
        $paramsMap = $payUtil->signParams($paramsMap, $this->paygateConfig);
        ksort($paramsMap);
        $resultMap = $httpRequest->httpJsonPost($url, $paramsMap);
//        $payUtil->verifyUqpayNotice($resultMap, $this->paygateConfig);
        return $resultMap;
    }

    private function redirectPost($url, $paramsMap)
    {
        $payUtil = new payUtil();
        $httpRequest = new httpRequest();
        ksort($paramsMap);
        $paramsMap = $payUtil->signParams($paramsMap, $this->paygateConfig);
        ksort($paramsMap);
        $resultMap = $httpRequest->httpRedirectArrayPost($url, $paramsMap);
        return $resultMap;
    }


    private function apiUrl($url)
    {
        return $this->paygateConfig->apiRoot . $url;
    }

    private function QRCodePayment($pay, $url, $scenes)
    {
        $payMethod = new payMethod();
        $UqpayScanType = $payMethod->UqpayScanType;
//        $this->validatePayData($pay);
        if (!array_key_exists('scanType', $pay)) Yii::warning("uqpay qr code payment need Scan Type");
        if (strcmp($pay["scanType"], $UqpayScanType["Merchant"]) == 0 && !array_key_exists('identity', $pay)) Yii::warning("uqpay qr code payment need the identity data when scan type is merchant");
        $payUtil = new payUtil();
        $paramsMap = $payUtil->generatePayParamsMap($pay, $this->merchantConfig);
        $result = $this->directFormPost($url, $paramsMap);
        return $result;
    }

    private function OfflineQRCodePayment($pay, $url, $scenes)
    {
        if ($pay["identity"] == null)
            Yii::warning("uqpay offline qr code payment need the identity data");
        if ($pay["merchantCity"] == null) {
            Yii::warning("uqpay offline qr code payment need the merchant city data");
        }
        if ($pay["terminalID"] == null) {
            Yii::warning("uqpay offline qr code payment need the terminal id data");
        }
        $paramsMap = $this->generatePayParamsMap($pay, $scenes);
        return $this->directFormPost($url, $paramsMap);
    }

    private function RedirectPayment($payOptions, $url, $scenes)
    {
        if ($payOptions["returnUrl"] == null || $payOptions["returnUrl"] == "") {
            Yii::warning("uqpay online payment need sync notice url");
        }
        $payUtil = new payUtil();
        $paramsMap = $this->generatePayParamsMap($payOptions, $scenes);
        $paramsMap[PAY_OPTIONS_SYNC_NOTICE_URL] = $payOptions["returnUrl"];
        $transResult = new TransResult($paramsMap, $url, $scenes);
        return $transResult;
    }


    private function MerchantHostPayment($pay, $url, $scenes)
    {
        $paramsMap = $this->generatePayParamsMap($pay, $scenes);
        return $this->directFormPost($url, $paramsMap);
    }

    private function ServerHostPayment($pay, $url, $scenes)
    {
        $paramsMap = $this->generatePayParamsMap($pay, $scenes);
        return $this->directFormPost($url, $paramsMap);
    }

    private function CreditCardPayment($payData, $url, $scenes)
    {
        $paramsMap = $this->generatePayParamsMap($payData, $scenes);
        $result = $this->directFormPost($url, $paramsMap);
        return $result;
    }

    private function ThreeDSecurePayment($payData, $url, $scenes)
    {
        if ($payData["returnUrl"] == null || strcmp($payData["returnUrl"], "") == 0)
            Yii::warning("uqpay 3D secure payment need sync notice url");
        $paramsData = $this->generatePayParamsMap($payData, $scenes);
        $transResult = new TransResult($paramsData, $url, $scenes);
        return $transResult;
    }


    private function InAppPayment($payData, $url, $scenes)
    {
        if ($payData["clientType"] == null) Yii::warning("client type is required for uqpay in-app payment");
        $payMethod = new payMethod();
        $paymentSupportClient = $payMethod->paymentSupportClient;
        if (strcmp($payData["clientType"], $paymentSupportClient["PC_WEB"]) == 0) Yii::warning("uqpay in-app payment not support pc clientType");
        $payUtil = new payUtil();
        $paramsMap = $this->generatePayParamsMap($payData, $scenes);
        $result = $this->directFormPost($url, $paramsMap);
        return $result;
    }

    private function PreAuthFinish(PreAuthOrder $order)
    {
        if ($order->uqOrderId <= 0) {
            Yii::warning("uqpay order id is required");
        }
        $paramsMap = array();
        $paramsMap["transName"] = $order->transName;
        $paramsMap["uqOrderId"] = $order->uqOrderId;
        return $this->directFormPost($this->apiUrl(PAYGATE_API_PRE_AUTH), $paramsMap);
    }

    private function EnrollCard(EnrollOrder $order)
    {
        $paramsMap = array();
        $paramsMap["orderId"] = $order->orderId;
        $paramsMap["date"] = $order->date;
        $paramsMap["verifyCode"] = $order->verifyCode;
        $paramsMap["codeOrderId"] = $order->codeOrderId;
        return $this->directFormPost($this->apiUrl(PAYGATE_API_ENROLL), $paramsMap);
    }

    private function VerifyPhone(VerifyOrder $order)
    {
        $payMethodObject = new payMethod();
        $UqpayTradeType = $payMethodObject->UqpayTradeType;
        $order->tradeType = $UqpayTradeType["verifycode"];
        return $this->directFormPost($this->apiUrl(PAYGATE_API_VERIFY), $order);
    }

    private function Refund($refund)
    {
//        $this->validatePayData($refund, "refund request data invalid for uqpay order operation");
        $payUtil = new payUtil();
        $paramsMap = $payUtil->generateRefundParams($refund, $this->merchantConfig);
        ksort($paramsMap);
        $paramsMap = $payUtil->signParams($paramsMap, $this->paygateConfig);
        $result = $this->directFormPost($this->apiUrl(PAYGATE_API_REFUND), $paramsMap);
        return $result;
    }

    private function Cancel($cancel)
    {
//        $this->validatePayData($cancel, "cancel payment request data invalid for uqpay order operation");
        $payUtil = new payUtil();
        $paramsMap = $payUtil->generateCancelParams($cancel, $this->merchantConfig);
        $result = $this->directFormPost($this->apiUrl(PAYGATE_API_CANCEL), $paramsMap);
        return $result;
    }

    private function Query($query)
    {
//        $this->validatePayData($query, "query request data invalid for uqpay order operation");
        $payUtil = new payUtil();
        $paramsMap = $payUtil->generateQueryParams($query, $this->merchantConfig);
        $result = $this->directFormPost($this->apiUrl(PAYGATE_API_QUERY), $paramsMap);
        return $result;
    }


//===========================================
// Pay API
//===========================================

    /**
     * @param $order
     * @return mixed|null|TransResult
     */

//PayOrder $order
    public function Pay($options)
    {
        $order = new PayOrder();
        $order->attributes = $options;
        if ($order->validate()) {
            $payMethodObject = new payMethod();
            $UqpayTradeType = $payMethodObject->UqpayTradeType;
            $order["transType"] = $UqpayTradeType["pay"];
            $payMethod = $payMethodObject->payMethod();
            $scenes = $payMethod[$order["methodId"]];
            switch ($scenes) {
                case "QRCode":
                    return $result = $this->QRCodePayment($options, $this->apiUrl(PAYGATE_API_PAY), $scenes);
                    break;
                case "OfflineQRCode":
                    return $this->OfflineQRCodePayment($options, $this->paygateApiUrl(PAYGATE_API_PAY), $scenes);
                case "OnlinePay":
                    return $this->RedirectPayment($options, $this->paygateApiUrl(PAYGATE_API_PAY), $scenes);
                case "InApp":
                    return $result = $this->InAppPayment($options, $this->apiUrl(PAYGATE_API_PAY), $scenes);
                    break;
                case "CreditCard":
                    switch ($options->methodId) {
                        case $payMethodObject->AMEX:
                        case $payMethodObject->JCB:
                        case $payMethodObject->Master:
                        case $payMethodObject->VISA:
                            $bankCard = new BankCardDTO();
                            $bankCard->attributes=$options;
                            if($bankCard->validate()){
                                $order->bankCard=$bankCard;
                            }else{
                                $errors = $bankCard->errors;
                                var_dump($errors);
                                return $errors;
                            }
                            break;
                        default:
                    }
                    return $this->CreditCardPayment($options, $this->apiUrl(PAYGATE_API_PAY), $scenes);
                case "ThreeDCreditCard":
                    return $this->ThreeDSecurePayment($options, $this->apiUrl(PAYGATE_API_PAY), $scenes);
                case "MerchantHost":
                    $merchantHost = new MerchantHostDTO();
                    $merchantHost->attributes=$options;
                    if($merchantHost->validate()){
                        $order->merchantHost=$merchantHost;
                        return $this->MerchantHostPayment($options, $this->apiUrl(PAYGATE_API_PAY), $scenes);
                    }else{
                        $errors = $merchantHost->errors;
                        var_dump($errors);
                        return $errors;
                    }
                case "ServerHost":
                    $serverHost = new ServerHostDTO();
                    $serverHost->attributes=$options;
                    if($serverHost->validate()){
                        $order->merchantHost=$serverHost;
                        return $this->ServerHostPayment($options, $this->apiUrl(PAYGATE_API_PAY), $scenes);
                    }else{
                        $errors = $serverHost->errors;
                        var_dump($errors);
                        return $errors;
                    }
                default:
                    $result = null;
            }
            return $result;
        } else {
            $errors = $order->errors;
            return $errors;
        }
    }




    function preAuth(PreAuthOrder $order)
    {
        switch ($order->transType) {
            case "preauth":
                $payMethodObject = new payMethod();
                $payMethod = $payMethodObject->payMethod();
                $scenes = $payMethod[$order["methodId"]];
                switch ($scenes) {
                    case "InApp":
                        return $this->InAppPayment($order, $this->paygateApiUrl(PAYGATE_API_PRE_AUTH), $scenes);
                    case "CreditCard":
                        return $this->CreditCardPayment($order, $this->paygateApiUrl(PAYGATE_API_PRE_AUTH), $scenes);
                    case "OnlinePay":
                        return $this->ThreeDSecurePayment($order, $this->paygateApiUrl(PAYGATE_API_PRE_AUTH), $scenes);
                    case "MerchantHost":
                        return $this->MerchantHostPayment($order, $this->apiUrl(PAYGATE_API_PRE_AUTH), $scenes);
                    case "ServerHost":
                        return $this->MerchantHostPayment($order, $this->apiUrl(PAYGATE_API_PRE_AUTH), $scenes);
                    default:
                        return null;
                }
            case "preauthcc":
            case "preauthcomplete":
            case "preauthcancel":
                return $this->PreAuthFinish($order);
            default:
                return null;
        }
    }



    //===========================================
    // Enroll API
    //===========================================

    function enroll(EnrollOrder $order)
    {
        $payMethodObject = new payMethod();
        $UqpayTradeType = $payMethodObject->UqpayTradeType;
        $order->transType = $UqpayTradeType["enroll"];

        $payMethodObject = new payMethod();
        $payMethod = $payMethodObject->payMethod();
        $scenes = $payMethod[$order["methodId"]];
        switch ($scenes) {
            case "MerchantHost":
                return $this->EnrollCard($order);
            case "ServerHost":
                return $this->EnrollCard($order);
            default:
                return null;
        }
    }

    function verify(VerifyOrder $order)
    {
        $payMethodObject = new payMethod();
        $UqpayTradeType = $payMethodObject->UqpayTradeType;
        $order->transType = $UqpayTradeType["verifycode"];
        return $this->VerifyPhone($order);
    }

    //===========================================
    // Merchant Register API
    //===========================================

    public function register(MerchantRegisterDTO $registerDTO){
        $this->setAuthForJsonParams($registerDTO);
        return $this->directJsonPost($registerDTO,$this->appgateApiUrl(APPGATE_API_REGISTER));
    }

//===========================================
// EMVCO QRCode API
//===========================================

    public function createQRCode(EmvcoCreateDTO $createDTO){
        $this->setAuthForJsonParams($createDTO);
        return $this->directJsonPost($createDTO,$this->appgateApiUrl(APPGATE_API_EMVCO_CREATE));
    }

    public function getQRCodePayload(EmvcoGetPayloadDTO $payloadDTO){
        $this->setAuthForJsonParams($payloadDTO);
        return $this->directJsonPost($payloadDTO,$this->appgateApiUrl(APPGATE_API_EMVCO_PAYLOAD));
    }

    //===========================================
    // UQPAY Public Resource API
    //===========================================

    public function queryExchangeRate(ExchangeRateQueryDTO $queryDTO){
        $this->setAuthForJsonParams($queryDTO);
        return $this->directJsonPost($queryDTO, $this->appgateApiUrl(APPGATE_API_RES_EXCHANGE_RATE));
    }

    //===========================================
    // Cashier API
    //===========================================
    public function generateCashierLink($cashier)
    {
        $paramsMap = $cashier;
        $payUtil = new payUtil();
        ksort($paramsMap);
        $paramsMap = $payUtil->signParams($paramsMap, $this->cashierConfig);
        return $this->cashierConfig['apiRoot'] ."?" . http_build_query($paramsMap);
    }


    public function actionIndex()
    {
//        $authDto = new AuthDTO();
//        $authDto->merchantId='1005004';

//        $model = new BankCardDTO();
        $options = new PayOptionsDTO();
        $options->attributes = \Yii::$app->request->get();
        $bankCard = new BankCardExtendDTO();
        var_dump($options);
//        var_dump(\Yii::$app->request->get());
        $bankCard->attributes = \Yii::$app->request->get();
//        var_dump($bankCard);
        $merchantHost = new MerchantHostDTO();
        $merchantHost->attributes = \Yii::$app->request->get();
//        var_dump($merchantHost);
        if ($merchantHost->validate()) {
            // 所有输入数据都有效 all inputs are valid

            echo(1111);
        } else {
            // 验证失败：$errors 是一个包含错误信息的数组
            $errors = $merchantHost->errors;
            var_dump($errors);
        }
        return;
        if ($authDto->validate()) {
            // 所有输入数据都有效 all inputs are valid

            echo(1111);
        } else {
            // 验证失败：$errors 是一个包含错误信息的数组
            $errors = $authDto->errors;
            print_r($errors);
        }
//        return $this->render('index');
    }
}
