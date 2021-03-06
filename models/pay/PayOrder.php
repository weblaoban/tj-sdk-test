<?php
namespace tj\sdk\test\models\pay;

use tj\sdk\test\models\common\OrderDTO;

class PayOrder extends OrderDTO
{
    public $transName;
    public $bankCard; // required when credit card payment   BankCardExtendDTO
    public $serverHost; // required when server host payment   ServerHostDTO
    public $merchantHost; // required when merchant host payment  MerchantHostDTO

    public function rules()
    {
        return array_merge(parent::rules(),
            [
                [['transName'],'required'],
//                [['bankCard','serverHost','merchantHost'],'']
            ]); // TODO: Change the autogenerated stub
    }
}
