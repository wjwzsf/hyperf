<?php


namespace App\Service;
use GuzzleHttp\Client;

class HttpRequestService
{
    /**
     * User: wujiawei
     * DateTime: 2023/5/23 16:41
     * describe:
     * @param $url      请求网址
     * @param $method   POST/GET
     * @param $param    GET请求传递的数组
     * @param $data     POST请求传递的数组
     * @return string
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function httpRequest($url,$method,$param=[],$data=[]){
        $client = new Client();
        $options = [
            'timeout' => 30,
            'verify' => false,
        ];
        if(strtoupper($method) == 'GET'){
            $options['query'] = $param;
        }
        if(strtoupper($method) == 'POST'){
            $options['form_params'] = $data;
            if(is_string($data)){ //发送JSON数据
                $options['headers'] = [
                    'Content-Type' => 'application/json; charset=utf-8',
                    'Content-Length' => strlen($data),
                ];
            }
        }
        $response = $client->request(strtoupper($method), $url, $options);
        $body = (string) $response->getBody();
        return json_decode($body,true);
    }
}