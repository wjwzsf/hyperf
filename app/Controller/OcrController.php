<?php
namespace App\Controller;

use Hyperf\HttpServer\Annotation\AutoController;//路由自动注册
use Hyperf\HttpServer\Contract\ResponseInterface;//输出ResponseInterface
use App\Utils\AiSdk\AipOcr;//引入ocr识别类

#[AutoController(prefix: "/ocrs")]
class OcrController extends AbstractController
{
    public function index(ResponseInterface $response)
    {
//        $config = [
//            'appId' => '17606770',
//            'apiKey' => 'mGqwCQCBfdweGbQTILshOz2r',
//            'secretKey' => 'cffxPsfr9RmD5vi2FHMI8hBmLmoxcZT5',
//        ];
        $ocr = new AipOcr('17606770','mGqwCQCBfdweGbQTILshOz2r','cffxPsfr9RmD5vi2FHMI8hBmLmoxcZT5');
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
        $result = $ocr->faceCheck(json_encode($options,JSON_PRETTY_PRINT));
        return $response->json($result);
    }
}