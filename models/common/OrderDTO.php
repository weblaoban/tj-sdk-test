<?php

namespace app\models\common;

class OrderDTO extends PayOptionsDTO
{
    /**
     * is required
     */
    public $orderId; // your order id
    public $amount;
    public $currency; // use ISO 4217 currency code same as the Java Currency Class
    public $date; // this order generate date
    public $clientIp;

    /**
     * not required
     */
    public $quantity; // quantity of products
    public $storeId; // your store id
    public $seller; // your seller id

    public function rules()
    {
        return array_merge([
            [['orderId', 'amount', 'currency', 'date','clientIp'], 'required'],
            [['quantity', 'storeId', 'seller'], 'string'],
            ['date', 'required'],
        ], parent::rules());
    }
}
