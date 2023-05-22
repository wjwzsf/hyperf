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
        //size name 上传到oss的文件路径
        $imgReturn = $this->uploadServer->uploadImage(['size'=>'4096','name'=>$params['type'],'ossurl'=>'Upload/V3/idcard/'.date('Y-m-d',time())]);
        switch ($imgReturn['code']){
            case 200:
                //识别功能
                $data=[
                    'type'=>$params['type'],
                    'localurl'=>$imgReturn['localurl']
                ];
                $checkData = $this->recognitionService->idcard($data);
                //检测身份证正反面功能
                $memberAuth = new \App\Dao\MemberAuth();
                $result = $memberAuth->checkIdcard($checkData,$params['type']);
                if ($result['code']==200){
                    //设置返回的地址
                    $result[$params['type'].'url']=$imgReturn['url'];
                }
                @unlink(BASE_PATH . $imgReturn['localurl']);
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