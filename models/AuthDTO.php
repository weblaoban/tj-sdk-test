<?php
namespace tj\sdk\test\models;
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
