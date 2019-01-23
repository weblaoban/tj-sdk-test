<?php

namespace tj\sdk\test\models\config;

use yii\base\Model;

class merchantConfig extends Model
{
    public $id;
    public $agentId = 0;

    public function rules()
    {
        return [
            ['id', 'required'],
            ['agentId', 'default', 'value' => 0],
        ];
    }
}
