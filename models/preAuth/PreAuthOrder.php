<?php

namespace tj\sdk\test\models\preAuth;

use tj\sdk\test\models\common\OrderDTO;

class PreAuthOrder extends OrderDTO
{
    public $transName; // product info
    public $uqOrderId;
    public $bankCard; // required when credit card payment
    public $serverHost; // required when server host payment
    public $merchantHost; // required when merchant host payment
}
