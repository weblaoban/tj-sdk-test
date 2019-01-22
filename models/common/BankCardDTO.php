<?php
namespace app\models\common;

use yii\base\Model;

class BankCardDTO extends Model {
    public $firstName;
    public $lastName;
    public $cvv;
    public $cardNum;
    public $expireMonth;
    public $expireYear;

    public function rules(){
        return [
            [['firstName','lastName','cvv','cardNum','expireMonth','expireYear'], 'required'],
        ];
    }
}
