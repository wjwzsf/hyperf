<?php
namespace App\Controller;

use Hyperf\HttpServer\Annotation\AutoController;
use Psr\Http\Message\ResponseInterface;
use App\Utils\AiSdk\AipOcr;

#[AutoController(prefix: "/ocrs")]
class OcrController extends AbstractController
{
    public function index(): ResponseInterface
    {
//        $config = [
//            'appId' => '17606770',
//            'apiKey' => 'mGqwCQCBfdweGbQTILshOz2r',
//            'secretKey' => 'cffxPsfr9RmD5vi2FHMI8hBmLmoxcZT5',
//        ];
        $ocr = new AipOcr('17606770','mGqwCQCBfdweGbQTILshOz2r','cffxPsfr9RmD5vi2FHMI8hBmLmoxcZT5');
        $filePath = BASE_PATH . '/public/images/16.png';
        $image = file_get_contents($filePath);
        $result = $ocr->basicGeneral($image);
        return $this->response->json($result);
    }
}