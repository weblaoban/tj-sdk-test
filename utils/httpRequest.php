<?php
namespace tj\sdk\test\utils;
use GuzzleHttp\Client;
use Yii;

class httpRequest
{
    function httpArrayPost($url, $data)
    {
        $client = new Client(["verify"=>false,"timeout"=>"2.0","http_errors"=>false]);
        $response = $client->post($url, ["form_params" => $data]);
        $code = $response->getStatusCode();
        return $response;
    }

    function httpRedirectArrayPost($url, $data){
        $client = new Client(["verify"=>false,"timeout"=>"2.0","http_errors"=>false]);
        $response = $client->post($url, ["form_params" => $data]);
        $code = $response->getStatusCode();
        if($code>=200&&$code<300){
            return (string) $response->getBody();
        }
        $result = json_decode((string)$response->getBody(),true);
        return ["code"=>$result->code,"message"=>$result->message];
    }

    function httpJsonPost($url, $data){
        $client = new Client(["verify"=>false,"timeout"=>"2.0","http_errors"=>false]);
        $response = $client->post($url, ["form_params" => \GuzzleHttp\json_encode($data),'headers' => [
            'Accept'     => 'application/json;charset=UTF-8',
        ]]);
        $code = $response->getStatusCode();
        if($code>=200&&$code<300){
            return (string) $response->getBody();
        }
        $result = json_decode((string)$response->getBody(),true);
        return ["code"=>$result->code,"message"=>$result->message];
    }
}
