<?php


namespace App\Controller;


use App\Service\TextRecognitionService;
use App\Service\UploadServer;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\PostMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\Validation\ValidatorFactory;

#[Controller]
class MemberAuth extends AbstractController
{


    /**
     * @var TextRecognitionService
     * 识别身份证正反面
     */
    #[Inject]
    private TextRecognitionService $recognitionService;
    #[Inject]
    private UploadServer $uploadServer;
    #[PostMapping(path: "idcard")]
    public function idcard(){
        // 获取所有参数和文件
        $params = $this->request->all();
        $imgurl = $this->uploadServer->uploadImage(['size'=>'10240','name'=>'front']);
        return $this->response->json($imgurl);

//        $data=[
//            'type'=>'front'
//        ];
//        $result = $this->recognitionService->idcard($data);
//        return $result;
    }

}