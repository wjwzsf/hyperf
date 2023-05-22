<?php


namespace App\Controller;


use App\Service\TextRecognitionService;
use App\Service\UploadServer;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\PostMapping;

#[Controller]
class MemberAuth extends AbstractController
{
    #[Inject]
    private TextRecognitionService $recognitionService;
    #[Inject]
    private UploadServer $uploadServer;

    /**
     * 识别身份证正反面
     */
    #[PostMapping(path: "idcard")]
    public function idcard(){
        // 获取所有参数和文件
        $params = $this->request->all();
        $imgReturn = $this->uploadServer->uploadImage(['size'=>'4096','name'=>$params['type']]);
        switch ($imgReturn['code']){
            case 200:
                //识别功能
                $data=[
                    'type'=>$params['type'],
                    'url'=>$imgReturn['url']
                ];
                $checkData = $this->recognitionService->idcard($data);
                //检测身份证正反面功能
                $memberAuth = new \App\Dao\MemberAuth();
                $result = $memberAuth->checkIdcard($checkData,$params['type']);
                if ($result['code']==200){
                    $result[$params['type'].'url']=$imgReturn['url'];
                }
                @unlink(BASE_PATH . $imgReturn['url']);
                return $result;
                break;
            case 400:
                //图片上传失败
                return $this->response->json($imgReturn);
                break;
        }
    }

    /**
     * User: wujiawei
     * DateTime: 2023/5/22 14:24
     * describe: 识别银行卡图片
     */
    #[PostMapping(path: "bankcard")]
    public function bankcard(){

    }

}