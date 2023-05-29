<?php


namespace App\Controller;


use App\Service\TextRecognitionService;
use App\Service\UploadServer;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\PostMapping;

#[Controller(prefix: "payMethod")]
class PayMethod extends AbstractController
{
    #[Inject]
    private TextRecognitionService $recognitionService;//ocr识别服务 通过依赖注入服务
    #[Inject]
    private UploadServer $uploadServer;//上传图片服务

    /**
     * User: wujiawei
     * DateTime: 2023/5/22 14:24
     * describe: 识别银行卡图片
     */
    #[PostMapping(path: "bankcard")]
    public function bankcard(){
        // 获取所有参数和文件
        $params = $this->request->all();
        if ($this->request->hasFile('bankcard')) {
            //size name 上传到oss的文件路径
            $imgReturn = $this->uploadServer->uploadImage(['size'=>'4096','name'=>'bankcard','ossurl'=>'Upload/V3/bankcard/'.date('Y-m-d',time())]);
            switch ($imgReturn['code']){
                case 200:
                    //识别功能
                    $data=[
                        'localurl'=>$imgReturn['localurl']
                    ];
                    $checkData = $this->recognitionService->bankcard($data);
                    @unlink(BASE_PATH . $imgReturn['localurl']);
                    return $checkData;
//                    //检测银行卡
//                    $memberAuth = new \App\Dao\MemberAuth();
//                    $result = $memberAuth->checkFace($checkData);
//                    if ($result['code']==200){
//                        //设置返回的地址
//                        $result['bankcardurl']=$imgReturn['url'];
//                    }
//                    @unlink(BASE_PATH . $imgReturn['localurl']);
//                    return $result;
                case 400:
                    //图片上传失败
                    return $this->response->json($imgReturn);
            }
        }else{
            return [
                'code'=>400,
                'message' => '请上传图片',
            ];
        }
    }

}