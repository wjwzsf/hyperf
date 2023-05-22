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
    public function idcard($data){
            $filePath = BASE_PATH . '/public/images/ws1.jpg';
            $image = file_get_contents($filePath);
            //idcard
            //$result = $ocr->idcard($image,'front');
            //card
            //$result = $ocr->bankcard($image);
            //shouchi
            //定义参数变量
            $options = array(
                "image"=>base64_encode(file_get_contents($filePath)),
                "image_type"=>"BASE64",
                "max_face_num"=>2 //最多处理人脸的数目，默认值为1 最大值10
            );
            $result = $this->ocr->faceCheck(json_encode($options,JSON_PRETTY_PRINT));
        return $result;
    }
}