<?php


namespace App\Service;
use AlibabaCloud\SDK\Dysmsapi\V20170525\Dysmsapi;
use Darabonba\OpenApi\Models\Config;
use AlibabaCloud\SDK\Dysmsapi\V20170525\Models\SendSmsRequest;

class AliSmsService
{
    const KEY = 'LTAIr59yEsCv2liA';
    const SECRET = 'uFDTNCjnMxDZmjpcTX1T9BVgyaQQKG';
    const SIGN = '灵工邦';
    /**
     * 使用AK&SK初始化账号Client
     * @param mixed $accessKeyId
     * @param mixed $accessKeySecret
     * @return Dysmsapi Client
     */
    public static function createClient($accessKeyId = null, $accessKeySecret = null)
    {
        $config = new Config([
            "accessKeyId" => $accessKeyId ?? AliSmsService::KEY,
            "accessKeySecret" => $accessKeySecret ?? AliSmsService::SECRET
        ]);
        // 访问的域名
        $config->endpoint = "dysmsapi.aliyuncs.com";
        return new Dysmsapi($config);
    }

    /**
     * 短信验证码
     * @param int $phone 手机号码
     * @param int $code 验证码
     * @return array
     */
    public static function verify(int $phone, int $code,String $templateCode='')
    {
        $client = self::createClient(AliSmsService::KEY, AliSmsService::SECRET);
        $sendSmsRequest = new SendSmsRequest([
            "phoneNumbers" => $phone,
            "signName" => AliSmsService::SIGN,
            "templateCode" => $templateCode ? : config('sms.templateCode'),
            "templateParam" => json_encode([
                'code' => $code
            ])
        ]);
        $result = $client->sendSms($sendSmsRequest);
        if ($result->body->message == 'OK' && $result->body->code == 'OK') {
            return ['status' => 1];
        }
        if ($result->body->code == 'isv.MOBILE_NUMBER_ILLEGAL') {
            return ['status' => 0, 'msg' => '手机号码格式不正确'];
        }
        return ['status' => 0, 'msg' => '短信发送失败，网络繁忙'];
    }

    /**
     * @param string[] $args
     * @return void
     */
    public static function main($args)
    {
        $client = self::createClient("accessKeyId", "accessKeySecret");
        $sendSmsRequest = new SendSmsRequest([
            "phoneNumbers" => "1503871****",
            "signName" => "阿里大于测试专用",
            "templateCode" => "SMS_215180185",
            "templateParam" => "{\"code\":\"1111\",\"code1\":\"1111\",\"code3\":\"1111\"}"
        ]);
        // 复制代码运行请自行打印 API 的返回值
        $client->sendSms($sendSmsRequest);
    }
}