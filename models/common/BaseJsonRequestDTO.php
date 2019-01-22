<?php
namespace app\models\common;

class BaseJsonRequestDTO extends AuthDTO
{
    public $signType=1;
    public $date;
    public $signature;

    public function rules(){
        return  array_merge(parent::rules(),[
                ['signType', 'default', 'value' => 1],
                ['signature', 'default', 'value' => '000000'],
                [['date'], 'date', 'required'],
            ]);

    }
}

