<?php

namespace app\models\common;
use yii\base\Model;


class AuthDTO extends Model{
    public $merchantId;
    public $agentId;

    public function rules()
    {
        return [
            [['merchantId'], 'required'],
        ];
    }
}
