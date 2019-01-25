<?php
namespace tj\sdk\test\utils;
use GuzzleHttp\Client;
use Yii;

class httpRequest
{
    function httpArrayPost($url, $data)
    {
        $client = new Client(["verify"=>false]);
        $response = $client->post($url, ["form_params" => $data]);
        $code = $response->getStatusCode();
        if($code>=200&&$code<300){
            return json_decode((string)$response->getBody(),true);
        }
        $result = json_decode((string)$response->getBody(),true);
        Yii::warning($result->code.''.$result->message);
    }

    function httpRedirectArrayPost($url, $data){
        $client = new Client(["verify"=>false]);
        $response = $client->post($url, ["form_params" => $data]);
        $code = $response->getStatusCode();
        if($code>=200&&$code<300){
            return (string) $response->getBody();
        }
        $result = json_decode((string)$response->getBody(),true);
        Yii::warning($result->code.''.$result->message);
    }

    function httpJsonPost($url, $data){
        $client = new Client(["verify"=>false]);
        $response = $client->post($url, ["form_params" => \GuzzleHttp\json_encode($data),'headers' => [
            'Accept'     => 'application/json;charset=UTF-8',
        ]]);
        $code = $response->getStatusCode();
        if($code>=200&&$code<300){
            return (string) $response->getBody();
        }
        $result = json_decode((string)$response->getBody(),true);
        Yii::warning($result->code.''.$result->message);
    }
}
