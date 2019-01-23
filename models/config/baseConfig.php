<?php

namespace tj\sdk\test\models\config;
use yii\base\Model;

class baseConfig extends Model
{
    public $testMode = false;
    public $testRSA; //SecureConfig
    public $productRSA;//SecureConfig

    public function rules()
    {
        return [['testMode','default',false]]; // TODO: Change the autogenerated stub
    }

    public function getRSA()
    {
        var_dump($this->testMode);
        if ($this->testMode) {
            return $this->testRSA;
        }
        return $this->productRSA;
    }

    public function isTestMode()
    {
        return $this->testMode;
    }
}
