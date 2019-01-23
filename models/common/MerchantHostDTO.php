<?php
namespace tj\sdk\test\models\common;

use yii\base\Model;

class MerchantHostDTO extends Model {
  public $cardNum;
  public $expireMonth;
  public $expireYear;
  public $phone;
    public function rules(){
        return [
            [['expireMonth','expireYear'], 'string'],
            [['cardNum','phone'], 'required'],
        ];
    }
}

