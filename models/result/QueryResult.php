<?php

namespace tj\sdk\test\models\result;

class QueryResult extends BaseResult
{
    protected $orderId;
    protected $uqOrderId;
    protected $relatedId; // 关联的订单ID，当发生退款、撤销时，对应订单
    protected $methodId;
    protected $state;
    protected $channelInfo;
    protected $extendInfo;


    function __construct($mapResult)
    {
        $this->code = (string)$mapResult[RESULT_CODE];
        $this->sign = (string)$mapResult[AUTH_SIGN];
        $this->merchantId = (int)(string)$mapResult[AUTH_MERCHANT_ID];
        $this->transType = $mapResult[PAY_OPTIONS_TRADE_TYPE];
        $this->message = (string)$mapResult[RESULT_MESSAGE];
        $this->date = $mapResult[ORDER_DATE];
        $this->orderId = (string)$mapResult[ORDER_ID];
        $this->uqOrderId = (int)(string)$mapResult[RESULT_UQPAY_ORDER_ID];
        $this->methodId = (int)(string)$mapResult[PAY_OPTIONS_METHOD_ID];
        $this->state = (string)$mapResult[RESULT_STATE];
        $this->channelInfo = (string)$mapResult[ORDER_CHANNEL_INFO];
        $this->extendInfo = json_encode($mapResult[ORDER_EXTEND_INFO]);
        $this->relatedId = (string)$mapResult[RESULT_UQPAY_RELATED_ID];
    }
}
