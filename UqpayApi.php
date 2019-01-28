<?php

namespace tj\sdk\test;

use tj\sdk\test\models\AuthDTO;
use tj\sdk\test\models\common\BankCardDTO;
use tj\sdk\test\models\common\BaseJsonRequestDTO;
use tj\sdk\test\models\common\MerchantHostDTO;
use tj\sdk\test\models\common\ServerHostDTO;
use tj\sdk\test\models\config\AppgateConfig;
use tj\sdk\test\models\config\cashierConfig;
use tj\sdk\test\models\config\merchantConfig;
use tj\sdk\test\models\config\paygateConfig;
use tj\sdk\test\models\emvco\EmvcoCreateDTO;
use tj\sdk\test\models\emvco\EmvcoGetPayloadDTO;
use tj\sdk\test\models\enroll\EnrollOrder;
use tj\sdk\test\models\enroll\VerifyOrder;
use tj\sdk\test\models\exchangeRate\ExchangeRateQueryDTO;
use tj\sdk\test\models\merchant\MerchantRegisterDTO;
use tj\sdk\test\models\operation\OrderCancel;
use tj\sdk\test\models\operation\OrderQuery;
use tj\sdk\test\models\operation\OrderRefund;
use tj\sdk\test\models\pay\PayOrder;
use tj\sdk\test\models\preAuth\PreAuthOrder;
use tj\sdk\test\models\result\TransResult;
use tj\sdk\test\utils\httpRequest;
use tj\sdk\test\utils\payMethod;
use tj\sdk\test\utils\payUtil;
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
        $this->auth = new AuthDTO();
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
        $paramsMap = $payUtil->generateDefPayParams($payData, $this->merchantConfig);
        switch ($scenes) {
            case "QRCode":
                $paramsMap[PAY_OPTIONS_SCAN_TYPE] = $payMethod->UqpayScanType[$payData->scanType];
                if ($payData->scanType === 0) {
                    $paramsMap["identity"] = $payData->identity;
                }
                break;
            case "OfflineQRCode":
                $paramsMap[PAY_OPTIONS_SCAN_TYPE] = $payMethod->UqpayScanType[$payData->scanType];
                $paramsMap[PAY_OPTIONS_MERCHANT_CITY] = $payData->merchantCity;
                $paramsMap[PAY_OPTIONS_TERMINALID_ID] = $payData->terminalId;
                break;
            case "OnlinePay":
                $paramsMap[PAY_OPTIONS_SYNC_NOTICE_URL] = $payData->returnUrl;
                break;
            case "InApp":
                $paramsMap[PAY_OPTIONS_SYNC_NOTICE_URL] = $payData->returnUrl;
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
        $result = $payUtil->signParams($paramsMap, $this->paygateConfig);
        $result["signtype"] = $payData->signType ? $payData->signType : (string)$payMethod->SignTypeEnum['RSA'];
        return $result;
    }


    private function directFormPost($url, $paramsMap)
    {
        $payUtil = new payUtil();
        $httpRequest = new httpRequest();
        $resultMap = $httpRequest->httpArrayPost($url, $paramsMap);
        $code = $resultMap->getStatusCode();
        $result = json_decode((string)$resultMap->getBody());
        if ($code >= 200 && $code < 300) {
            $payUtil->verifyUqpayNotice($result, $this->paygateConfig);
        }
        return $result;
    }

    private function directJsonPost($url, $paramsMap)
    {
        $payUtil = new payUtil();
        $httpRequest = new httpRequest();
        ksort($paramsMap);
        $paramsMap = $payUtil->signParams($paramsMap, $this->paygateConfig);
        ksort($paramsMap);
        $resultMap = $httpRequest->httpJsonPost($url, $paramsMap);
        $code = $resultMap->getStatusCode();
        $result = json_decode((string)$resultMap->getBody());
        if ($code >= 200 && $code < 300) {
            $payUtil->verifyUqpayNotice($result, $this->paygateConfig);
        }
        return $result;
    }


    private function apiUrl($url)
    {
        return $this->paygateConfig->apiRoot . $url;
    }

    private function QRCodePayment($pay, $url, $scenes)
    {
        $payMethod = new payMethod();
        $UqpayScanType = $payMethod->UqpayScanType;
        if (!$pay->scanType) {
            return json_encode(["code" => '400', "message" => "uqpay qr code payment need Scan Type"]);
        };
        if (strcmp($pay->scanType, $UqpayScanType["Merchant"]) == 0 && !$pay->identity) {
            return json_encode(["code" => '400', "message" => "uqpay qr code payment need the identity data when scan type is merchant"]);
        };
        $paramsMap = $this->generatePayParamsMap($pay, $scenes);
        $result = $this->directFormPost($url, $paramsMap);
        return $result;
    }

    private function OfflineQRCodePayment($pay, $url, $scenes)
    {
        if (!$pay->identity) {
            return json_encode(["code" => '400', "message" => "uqpay offline qr code payment need the identity data"]);
        }
        if (!$pay->merchantCity) {
            return json_encode(["code" => "400", "message" => "uqpay offline qr code payment need the merchant city data"]);
        }
        if (!$pay->terminalID) {
            return json_encode(["code" => "400", "message" => "uqpay offline qr code payment need the terminal id data"]);
        }
        $paramsMap = $this->generatePayParamsMap($pay, $scenes);
        return $this->directFormPost($url, $paramsMap);
    }

    private function RedirectPayment($payOptions, $url, $scenes)
    {
        if (!$payOptions->returnUrl || $payOptions->returnUrl == "") {
            return json_encode(["code" => "400", "message" => "uqpay online payment need sync notice url"]);
        }
        $paramsMap = $this->generatePayParamsMap($payOptions, $scenes);
        $paramsMap[PAY_OPTIONS_SYNC_NOTICE_URL] = $payOptions->returnUrl;
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
        if (!$payData->returnUrl || strcmp($payData->returnUrl, "") == 0) {
            return json_encode(["code" => "400", "message" => "uqpay 3D secure payment need sync notice url"]);
        }
        $paramsData = $this->generatePayParamsMap($payData, $scenes);
        $transResult = new TransResult($paramsData, $url, $scenes);
        return $transResult;
    }


    private function InAppPayment($payData, $url, $scenes)
    {
        if (!$payData->clientType) {
            return json_encode(["code" => "400", "message" => "client type is required for uqpay in-app payment"]);
        }
        $payMethod = new payMethod();
        $paymentSupportClient = $payMethod->paymentSupportClient;
        if (strcmp($payData->clientType, $paymentSupportClient["PC_WEB"]) == 0) {
            return json_encode(["code" => "400", "message" => "uqpay in-app payment not support pc clientType"]);
        }
        $paramsMap = $this->generatePayParamsMap($payData, $scenes);
        $result = $this->directFormPost($url, $paramsMap);
        return $result;
    }

    private function PreAuthFinish(PreAuthOrder $order)
    {
        if (!$order->uqOrderId || $order->uqOrderId <= 0) {
            return json_encode(["code" => "400", "message" => "uqpay order id is required"]);
        }
        $paramsMap = array();
        $paramsMap["transName"] = $order->transName;
        $paramsMap["uqOrderId"] = $order->uqOrderId;
        return $this->directFormPost($this->apiUrl(PAYGATE_API_PRE_AUTH), $paramsMap);
    }

    private function EnrollCard(EnrollOrder $order)
    {
        if($order->validate()){
            $payUtil = new payUtil();
            $paramsMap = $payUtil->generateEnrollCardParams($order,$this->merchantConfig);
            $paramsMap = $payUtil->signParams($paramsMap, $this->paygateConfig);
            return $this->directFormPost($this->apiUrl(PAYGATE_API_ENROLL), $paramsMap);
        }else{
            return $order->errors;
        }
    }

    private function VerifyPhone(VerifyOrder $order)
    {
        if ($order->validate()) {
            $payUtil = new payUtil();
            $paramsMap = $payUtil->generateVerifyPhonePayParams($order, $this->merchantConfig);
            $paramsMap = $payUtil->signParams($paramsMap, $this->paygateConfig);
            return $this->directFormPost($this->apiUrl(PAYGATE_API_VERIFY), $paramsMap);
        } else {
            return $order->errors;
        }
    }

    private function Refund(OrderRefund $refund)
    {
        if ($refund->validate()) {
            $payUtil = new payUtil();
            $paramsMap = $payUtil->generateRefundParams($refund, $this->merchantConfig);
            $paramsMap = $payUtil->signParams($paramsMap, $this->paygateConfig);
            $result = $this->directFormPost($this->apiUrl(PAYGATE_API_REFUND), $paramsMap);
            return $result;
        } else {
            return $refund->errors;
        }
    }

    private function Cancel(OrderCancel $cancel)
    {
        if ($cancel->validate()) {
            $payUtil = new payUtil();
            $paramsMap = $payUtil->generateRefundParams($cancel, $this->merchantConfig);
            $paramsMap = $payUtil->signParams($paramsMap, $this->paygateConfig);
            $result = $this->directFormPost($this->apiUrl(PAYGATE_API_CANCEL), $paramsMap);
            return $result;
        } else {
            return $cancel->errors;
        }
    }

    private function Query(OrderQuery $query)
    {
        if ($query->validate()) {
            $payUtil = new payUtil();
            $paramsMap = $payUtil->generateRefundParams($query, $this->merchantConfig);
            $paramsMap = $payUtil->signParams($paramsMap, $this->paygateConfig);
            $result = $this->directFormPost($this->apiUrl(PAYGATE_API_QUERY), $paramsMap);
            return $result;
        } else {
            return $query->errors;
        }
    }


//===========================================
// Pay API
//===========================================

    /**
     * @param PayOrder $order
     * @return mixed|null|TransResult
     */

//PayOrder $order
    public function Pay(PayOrder $order)
    {
        if ($order->validate()) {
            $payMethodObject = new payMethod();
            $UqpayTradeType = $payMethodObject->UqpayTradeType;
            $order->transType = $UqpayTradeType["pay"];
            $payMethod = $payMethodObject->payMethod();
            $scenes = $payMethod[$order->methodId];
            switch ($scenes) {
                case "QRCode":
                    return $result = $this->QRCodePayment($order, $this->apiUrl(PAYGATE_API_PAY), $scenes);
                    break;
                case "OfflineQRCode":
                    return $this->OfflineQRCodePayment($order, $this->paygateApiUrl(PAYGATE_API_PAY), $scenes);
                case "OnlinePay":
                    return $this->RedirectPayment($order, $this->paygateApiUrl(PAYGATE_API_PAY), $scenes);
                case "InApp":
                    return $result = $this->InAppPayment($order, $this->apiUrl(PAYGATE_API_PAY), $scenes);
                    break;
                case "CreditCard":
                    switch ($order->methodId) {
                        case $payMethodObject->AMEX:
                        case $payMethodObject->JCB:
                        case $payMethodObject->Master:
                        case $payMethodObject->VISA:
                            $bankCard = new BankCardDTO();
                            $bankCard->attributes = $order->bankCard;
                            if ($bankCard->validate()) {
                                return $this->CreditCardPayment($order, $this->apiUrl(PAYGATE_API_PAY), $scenes);
                            } else {
                                $errors = $bankCard->errors;
                                return $errors;
                            }
                            break;
                        default:
                            return;
                    }
                case "ThreeDCreditCard":
                    return $this->ThreeDSecurePayment($order, $this->apiUrl(PAYGATE_API_PAY), $scenes);
                case "MerchantHost":
                    $merchantHost = new MerchantHostDTO();
                    $merchantHost->attributes = $order->merchantHost;
                    if ($merchantHost->validate()) {
                        return $this->MerchantHostPayment($order, $this->apiUrl(PAYGATE_API_PAY), $scenes);
                    } else {
                        $errors = $merchantHost->errors;
                        return $errors;
                    }
                case "ServerHost":
                    $serverHost = new ServerHostDTO();
                    $serverHost->attributes = $order;
                    if ($serverHost->validate()) {
                        $order->merchantHost = $serverHost;
                        return $this->ServerHostPayment($order, $this->apiUrl(PAYGATE_API_PAY), $scenes);
                    } else {
                        $errors = $serverHost->errors;
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
                $scenes = $payMethod[$order->methodId];
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
        return $this->VerifyPhone($order);
    }

    //===========================================
    // Merchant Register API
    //===========================================

    public function register(MerchantRegisterDTO $registerDTO)
    {
        $this->setAuthForJsonParams($registerDTO);
        return $this->directJsonPost($registerDTO, $this->appgateApiUrl(APPGATE_API_REGISTER));
    }

//===========================================
// EMVCO QRCode API
//===========================================

    public function createQRCode(EmvcoCreateDTO $createDTO)
    {
        $this->setAuthForJsonParams($createDTO);
        return $this->directJsonPost($createDTO, $this->appgateApiUrl(APPGATE_API_EMVCO_CREATE));
    }

    public function getQRCodePayload(EmvcoGetPayloadDTO $payloadDTO)
    {
        $this->setAuthForJsonParams($payloadDTO);
        return $this->directJsonPost($payloadDTO, $this->appgateApiUrl(APPGATE_API_EMVCO_PAYLOAD));
    }

    //===========================================
    // UQPAY Public Resource API
    //===========================================

    public function queryExchangeRate(ExchangeRateQueryDTO $queryDTO)
    {
        $this->setAuthForJsonParams($queryDTO);
        return $this->directJsonPost($queryDTO, $this->appgateApiUrl(APPGATE_API_RES_EXCHANGE_RATE));
    }

    //===========================================
    // Cashier API
    //===========================================
//    public function generateCashierLink($cashier)
//    {
//        $paramsMap = $cashier;
//        $payUtil = new payUtil();
//        ksort($paramsMap);
//        $paramsMap = $payUtil->signParams($paramsMap, $this->cashierConfig);
//        return $this->cashierConfig['apiRoot'] ."?" . http_build_query($paramsMap);
//    }


    public function actionIndex()
    {
        echo 'welcome';
    }
}
