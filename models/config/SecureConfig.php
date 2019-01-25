<?php

namespace tj\sdk\test\models\config;
use Yii;
class SecureConfig
{
    public $privateKeyStr;
    /**
     * The pem file path of RSA Private Key
     * Tips: make sure you have the permission to read.
     */
    public $privateKeyPath;

    public $publicKeyStr;

    public $publicKeyPath;

    public $privateKey;

    public $publicKey;

    function __construct($config)
    {
        $this->publicKeyPath=$config["publicKeyPath"];
        $this->privateKeyPath=$config["privateKeyPath"];
    }

    public function getPrivateKey()
    {
        if ($this->privateKey != null) {
            return $this->privateKey;
        }

        if (strcmp($this->privateKeyPath, "") != 0) {
            $this->privateKey = file_get_contents($this->privateKeyPath);
        } else if (strcmp($this->privateKeyPath, "") == 0) {
            $this->privateKey = $this->privateKeyStr;
        }
        if ($this->privateKey == null) {
            return json_encode(["code"=>"400","message"=>"Load Uqpay Payment Private Key Fail!"]);
        }
        return $this->privateKey;
    }

    public function getPublicKey()
    {
        if ($this->publicKey) {
            return $this->publicKey;
        }

        if (strcmp($this->publicKeyPath, "") != 0) {
            $this->publicKey = file_get_contents($this->publicKeyPath);
        } else if (strcmp($this->publicKeyPath, "") == 0) {
            $this->publicKey = $this->publicKeyStr;
        }
        if (!$this->publicKey) {
            return json_encode(["code"=>"400","message"=>"Load Uqpay Payment Public Key Fail!"]);
        }
        return $this->publicKey;
    }
}
