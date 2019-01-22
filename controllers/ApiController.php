<?php

namespace app\controllers;

use app\models\common\BankCardDTO;
use app\models\common\BankCardExtendDTO;
use app\models\common\BaseJsonRequestDTO;
use app\models\common\MerchantHostDTO;
use app\models\common\PayOptionsDTO;
use app\models\config\AppgateConfig;
use app\models\config\cashierConfig;
use app\models\config\merchantConfig;
use app\models\config\paygateConfig;
use app\models\config\SecureConfig;
use app\models\pay\PayOrder;
use app\utils\httpRequest;
use app\utils\payMethod;
use app\utils\payUtil;
use yii\web\Controller;
use app\models\common\AuthDTO;
use Yii;
use app\UqpayApi;

class ApiController extends Controller
{
    public $uqpay;

    public function init()
    {
        parent::init();
        $paygateConfig = new paygateConfig();
        $paygateConfig->testMode = true;
        $paygateConfig->testRSA=new SecureConfig(['publicKeyPath'=>'11111111','privateKeyPath'=>'1005004_prv.pem']);
        $merchantConfig = new merchantConfig();
        $merchantConfig->id='1005004';
        $cashierConfig = new cashierConfig();
        $appgateConfig = new AppgateConfig();
//        var_dump($paygateConfig->errors);
//        var_dump($merchantConfig->errors);
//        var_dump($cashierConfig->errors);
//        var_dump($appgateConfig->errors);
        $this->uqpay = new UqpayApi($paygateConfig,$merchantConfig,$cashierConfig,$appgateConfig);
    }



//===========================================
// Pay API
//===========================================

    /**
     * @param $order
     * @return mixed|null|TransResult
     */

//PayOrder $order
    public function actionPay()
    {
        $order=[
            'transName'=>'goods',
            'orderId'=>'123432543',
            'amount'=>111,
            'currency'=>'USD',
            'date'=> 1548058882332,
            'clientIp'=>'127.0.0.1',
            'transType'=>'pay',
            'methodId'=>'1003',
            'callbackUrl'=>'111',
            'clientType'=>'1',
            'scanType'=>'1'
        ];
        $this->uqpay->Pay($order);
    }

    public function actionIndex()
    {
//        $authDto = new AuthDTO();
//        $authDto->merchantId='1005004';

//        $model = new BankCardDTO();
        $options = new PayOptionsDTO();
        $options->attributes = \Yii::$app->request->get();
        $bankCard = new BankCardExtendDTO();
        var_dump($options);
//        var_dump(\Yii::$app->request->get());
        $bankCard->attributes = \Yii::$app->request->get();
//        var_dump($bankCard);
        $merchantHost = new MerchantHostDTO();
        $merchantHost->attributes = \Yii::$app->request->get();
//        var_dump($merchantHost);
        if ($merchantHost->validate()) {
            // 所有输入数据都有效 all inputs are valid

            echo(1111);
        } else {
            // 验证失败：$errors 是一个包含错误信息的数组
            $errors = $merchantHost->errors;
            var_dump($errors);
        }
        return;
        if ($authDto->validate()) {
            // 所有输入数据都有效 all inputs are valid

            echo(1111);
        } else {
            // 验证失败：$errors 是一个包含错误信息的数组
            $errors = $authDto->errors;
            print_r($errors);
        }
//        return $this->render('index');
    }
}
