<?php
namespace tj\sdk\test\models\common;

use app\models\PayOptions;

class PayOptionsDTO extends PayOptions {
  /**
   * is required
   */

  public $methodId;

  public $callbackUrl; // async notice url

  public $clientType; // only required for in app payment

  public $transType;

  /**
   * not required for each payment API
   */
  public $returnUrl; // sync notice url
  public $scanType; // only required for qr code payment
  public $identity; // only required for qr code payment when scan type is Merchant

  public $channelInfo;
  public $extendInfo;


    public function rules()
    {
        return array_merge([
            [['transType','methodId','callbackUrl','clientType'], 'required'],
        ],parent::rules());
    }
}
