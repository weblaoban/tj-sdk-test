<?php

namespace tj\sdk\test\utils;


class payMethod
{
    public $UnionPayQR = 1001;
    public $AlipayQR = 1002;
    public $WeChatQR = 1003;
    public $UnionPayOfflineQR = 1004;
    public $WechatWebBasedInApp = 1102;
    public $UnionSecurePay = 1100;
    public $UnionPayMerchantHost = 1101;
    public $UnionPayServerHost = 1103;
    public $VISA = 1200;
    public $VISA3D = 2500;
    public $Master = 1201;
    public $Master3D = 2501;
    public $UnionPayExpressPay = 1202;
    public $AMEX = 1203;
    public $JCB = 1204;
    public $PayPal = 1300;
    public $Alipay = 1301;
    public $AlipayWap = 1501;
    public $Wechat_InAPP = 2000;
    public $UnionPay_InAPP = 2001;
    public $ApplePay = 3000;

    public $scenesEnum = array(
        "RedirectPay" => "RedirectPay",//在线支付（跳转）
        "DirectPay" => "DirectPay",//在线支付（直接返回结果）
        "MerchantHost" => "MerchantHost",//存在验证环节的支付，如银联鉴权支付
        "EmbedPay" => "EmbedPay",//嵌入方式支付
        "QRCode" => "QRCode",
        "CreditCard" => "CreditCard",
        "ThreeDCreditCard" => "ThreeDCreditCard",
        "InApp" => "InApp",
        "OnlinePay" => "OnlinePay", // 104
        "ServerHost" => "ServerHost"// 111
    );
    public $SignTypeEnum = array(
        "RSA" => 1,
        "MD5" => 2,
    );

    public $UqpayScanType = array(
        "Merchant" => 0,//merchant
        "Consumer" => 1,//Consumer
    );

    public $BankCardType = array(
        "Debit" => 1,//借记卡
        "Credit" => 2,//信用卡
    );
    public $paymentSupportClient = array(
        "PC_WEB" => 1,
        "IOS" => 2,
        "Android" => 3,
    );

    public $QRCodeChannelTypeEnum = array(
        "UnionPay" => 1,
    );
    public $QRCodeTypeEnum = array(
        "Static" => 11,
        "Dynamic" => 12,
    );
    public $UqpayTradeType = array(
        "pay" => "pay",
        "cancel" => "cancel",
        "refund" => "refund",
        "preauth" => "preauth",
        "preauthcomplete" => "preauthcomplete",
        "preauthcancel" => "preauthcancel",
        "preauthcc" => "preauthcc",
        "verifycode" => "verifycode",
        "enroll" => "enroll",
        "withdraw" => "withdraw",
        "query" => "query"
    );

    function payMethod()
    {
        $payMethod = array(
            $this->UnionPayQR => $this->scenesEnum["QRCode"],
            $this->AlipayQR => $this->scenesEnum["QRCode"],
            $this->WeChatQR => $this->scenesEnum["QRCode"],
            $this->WechatWebBasedInApp => $this->scenesEnum["OnlinePay"],
            $this->UnionSecurePay => $this->scenesEnum["OnlinePay"],
            $this->VISA => $this->scenesEnum["CreditCard"],
            $this->VISA3D => $this->scenesEnum["ThreeDCreditCard"],
            $this->Master => $this->scenesEnum["CreditCard"],
            $this->Master3D => $this->scenesEnum["ThreeDCreditCard"],
            $this->UnionPayExpressPay => $this->scenesEnum["CreditCard"],
            $this->AMEX => $this->scenesEnum["CreditCard"],
            $this->JCB => $this->scenesEnum["CreditCard"],
            $this->PayPal => $this->scenesEnum["CreditCard"],
            $this->Alipay => $this->scenesEnum["OnlinePay"],
            $this->AlipayWap => $this->scenesEnum["OnlinePay"],
            $this->Wechat_InAPP => $this->scenesEnum["InApp"],
            $this->UnionPay_InAPP => $this->scenesEnum["InApp"],
            $this->ApplePay => $this->scenesEnum["OnlinePay"],
            $this->UnionPayMerchantHost => $this->scenesEnum["MerchantHost"],
            $this->UnionPayServerHost => $this->scenesEnum["ServerHost"]
        );
        return $payMethod;
    }
}
