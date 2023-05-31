<?php


namespace App\Service;
use AlibabaCloud\SDK\Dysmsapi\V20170525\Dysmsapi;
use Darabonba\OpenApi\Models\Config;
use AlibabaCloud\SDK\Dysmsapi\V20170525\Models\SendSmsRequest;
use Hyperf\Context\ApplicationContext;
class AliSmsService
{
    private $key;
    private $secret;
    private $sign;
    private $tempcode;
    public function __construct()
    {
        $this->key = env('SMS_KEY');
        $this->secret = env('SMS_KEY');
        $this->sign = env('SMS_KEY');
        $this->tempcode = env('SMS_TEMPCODE');
    }

    /**
     * 使用AK&SK初始化账号Client
     * @param mixed $accessKeyId
     * @param mixed $accessKeySecret
     * @return Dysmsapi Client
     */
    public static function createClient($accessKeyId = null, $accessKeySecret = null)
    {
        $config = new Config([
            "accessKeyId" => $accessKeyId,
            "accessKeySecret" => $accessKeySecret
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
    public function verify(int $phone, int $code,array $options,String $templateCode='')
    {
        $client = self::createClient($this->key, $this->secret);
        $sendSmsRequest = new SendSmsRequest([
            "phoneNumbers" => $phone,
            "signName" => $this->sign,
            "templateCode" => $templateCode ? : $this->tempcode,
            "templateParam" => json_encode([
                'code' => $code
            ])
        ]);
        $result = $client->sendSms($sendSmsRequest);
        if ($result->body->message == 'OK' && $result->body->code == 'OK') {
            $redis = ApplicationContext::getContainer()->get(\Hyperf\Redis\Redis::class);
            $redis->set($options['key'],$options['value'],$options['expiry']);
            return ['code' => 200,'message'=>'发送成功'];
        }
        if ($result->body->code == 'isv.MOBILE_NUMBER_ILLEGAL') {
            return ['code' => 400, 'message' => '手机号码格式错误'];
        }
        return ['code' => 400, 'message' => '短信发送失败，网络繁忙'];
    }
}