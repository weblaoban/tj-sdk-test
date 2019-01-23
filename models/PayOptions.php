<?php
namespace tj\sdk\test\models;
use yii\base\Model;

class PayOptions extends Model
{
    public $transType;
    public $methodId;
    public $callbackUrl;
    public $clientType;
    public $returnUrl;
    public $scanType;
    public $identity;
    public $channelInfo;
    public $extendInfo;

    public function rules()
    {
        return [
            [['transType','methodId','callbackUrl','clientType','returnUrl','scanType','identity'], 'string'],
//            [['channelInfo','extendInfo'], ''],
        ];
    }
}
