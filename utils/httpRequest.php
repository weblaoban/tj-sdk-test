<?php
namespace tj\sdk\test\utils;
use GuzzleHttp\Client;
use Yii;

class httpRequest
{
    function httpArrayPost($url, $data)
    {
        $client = new Client(["verify"=>false,"timeout"=>"6.0","http_errors"=>false]);
        $response = $client->post($url, ["form_params" => $data]);
        return $response;
    }

    function httpRedirectArrayPost($url, $data){
        $client = new Client(["verify"=>false,"timeout"=>"6.0","http_errors"=>false]);
        $response = $client->post($url, ["form_params" => $data]);
        return $response;
    }

    function httpJsonPost($url, $data){
        $client = new Client(["verify"=>false,"timeout"=>"6.0","http_errors"=>false]);
        $response = $client->post($url, ["form_params" => \GuzzleHttp\json_encode($data),'headers' => [
            'Accept'     => 'application/json;charset=UTF-8',
        ]]);
        return $response;
    }
}
