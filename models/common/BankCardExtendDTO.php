<?php
namespace tj\sdk\test\models\common;

class BankCardExtendDTO extends BankCardDTO {
  public $addressCountry;
    public $email;
    public function rules(){
        parent::rules();
        return array_merge(parent::rules(),[
            ['addressCountry', 'required'],
            ['email', 'email'],
        ]);
    }
}
