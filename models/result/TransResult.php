<?php
namespace tj\sdk\test\models\result;


class TransResult extends BaseResult {
  public $orderId;
  public $uqOrderId; // this order id generate by uqpay
  public $amount;
  public $currency;
  public $state; // order state
  public $channelInfo;
  public $extendInfo;

  /**
   * this result valued when IN-APP payment
   */
  public $acceptCode;// this code will be used by UQPAY Mobile Client SDK to generate the wallet app url scheme

  /**
   * these results valued when the payment want return some channel info
   */
  public $channelCode;
  public $channelMsg;

  /**
   * these results valued when QRCode payment
   */
  public $scanType;
  public $qrCodeUrl;
  public $qrCode;

  /**
   * this result only valued when ThreeD CreditCard and Online Payment
   * if this result is valued, the others will be null
   * user can return this data to client, and post them with media type "application/x-www-form-urlencoded" to the apiURL (which u can get from this data)
   */
  public $redirectPostData;


  function __construct($postData, $url, $scenes) {
      switch ($scenes){
          case 'InApp':
              $this->acceptCode=$postData['acceptCode'];
              break;
          case 'QRCode':
              $this->scanType=$postData['scanType'];
              $this->qrCodeUrl=$postData['qrCodeUrl'];
              $this->qrCode=$postData['qrCode'];
              break;
          case 'ThreeDCreditCard':
          case 'OnlinePay':
              $redirectPost = new RedirectPostData();
              $redirectPost->apiURL=$url;
              $redirectPost->postData=$postData;
              $this->redirectPostData = $redirectPost;
              break;
          default:
      }
  }
}
