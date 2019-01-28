<?php
namespace tj\sdk\test\models\enroll;


use tj\sdk\test\models\common\PayOptionsDTO;

class EnrollOrder extends PayOptionsDTO {
  public $orderId; // your order id
  public $date; // this order generate date
  public $verifyCode; // the verify code you get after request the verify api
  public $codeOrderId;// the uqpay order id, when you request for the verify code you will get it
  public $cvv;
  public $cardNum;
  public $expireYear;
  public $expireMonth;
  public $phone;
  public $clientIp;
  public $transName;

  public function rules()
  {
      return array_merge(
          [
              [['orderId','date','verifyCode','codeOrderId','cardNum','clientIp','transName'],'required'],
              ['date','date']
          ],
          parent::rules()); // TODO: Change the autogenerated stub
  }
}
