<?php
/**
 * orc识别类
 */
namespace App\Service;

use App\Utils\AiSdk\AipOcr;//引入ocr识别类
use Hyperf\HttpServer\Contract\ResponseInterface;//输出ResponseInterface

class TextRecognitionService
{
    private $ocr;
    private $response;
    public function __construct(ResponseInterface $response)
    {
        $this->response=$response;
        $this->ocr = new AipOcr('17606770','mGqwCQCBfdweGbQTILshOz2r','cffxPsfr9RmD5vi2FHMI8hBmLmoxcZT5');
    }

    public function recognize($filepath)
    {
        $image = file_get_contents($filepath);
        $result = $this->client->basicGeneral($image);
        return $result;
    }

    /**
     * User: wujiawei
     * DateTime: 2023/5/22 14:41
     * describe:身份证检测
     * @param $data
     * @return array
     */
    public function idcard($data){
        $filePath = BASE_PATH . $data['url'];
        $image = file_get_contents($filePath);
        $result = $this->ocr->idcard($image,$data['type']);
        return $result;
    }

    /**
     * User: wujiawei
     * DateTime: 2023/5/22 14:39
     * describe:手持检测
     * @param $data
     * @return \App\Utils\AiSdk\mix|bool|mixed|string[]
     */
    public function faceCheck($data){
        $filePath = BASE_PATH . $data['url'];
        //定义参数变量
        $options = array(
            "image"=>base64_encode(file_get_contents($filePath)),
            "image_type"=>"BASE64",
            "max_face_num"=>2 //最多处理人脸的数目，默认值为1 最大值10
        );
        $result = $this->ocr->faceCheck(json_encode($options,JSON_PRETTY_PRINT));
        return $result;
    }

    /**
     * User: wujiawei
     * DateTime: 2023/5/22 14:41
     * describe:银行卡检测
     * @param $data
     */
    public function bankcard($data){
        $filePath = BASE_PATH . $data['url'];
        $image = file_get_contents($filePath);
        $result = $this->ocr->bankcard($image);
    }
}