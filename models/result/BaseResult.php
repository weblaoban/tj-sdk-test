<?php

namespace app\models\result;

use yii\base\Model;

class BaseResult extends Model
{
    public $code;
    public $message;
    public $sign;
    public $merchantId;
    public $agentId;
    public $methodId;
    public $transType;
    public $date;

    function rules()
    {
        return [
            [['code','message'],'string','required']
        ]; // TODO: Change the autogenerated stub
    }
}