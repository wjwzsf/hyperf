<?php


namespace App\Controller;


use App\Service\TextRecognitionService;
use App\Service\UploadServer;
use Hyperf\DbConnection\Db;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
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
                    if(isset($checkData['error_code'])){
                        $result = [
                            'code'=>400,
                            'message'=>'识别失败，请重试'
                        ];
                    }else{
                        $bank_card_number = $checkData['result']['bank_card_number'];
                        $PayMethod = new \App\Dao\PayMethod();
                        //查询银行卡号是否重复
                        $cardCheck = $PayMethod->cardCheck($bank_card_number);
                        if ($cardCheck['code']==400){
                            $result = $cardCheck;
                        }else{
                            $result['code']=200;
                            $result['message']='识别成功';
                            $result['info']['bankcardurl']=$imgReturn['url'];
                            $result['info']['bank_card_number'] = $bank_card_number;
                            //顺带识别银行名称
                            $cardResult = $PayMethod->validateCard($bank_card_number);
                            if($cardResult['code']==200){
                                $result['info']['bankname'] = $cardResult['info']['bankname'];
                                $result['info']['abbreviation'] = $cardResult['info']['abbreviation'];
                            }
                        }
                    }
                    return $this->response->json($result);
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

    /**
     * User: wujiawei
     * DateTime: 2023/5/29 9:11
     * describe: 获取真实姓名
     */
    #[GetMapping(path: "realname")]
    public function getRealName(){
        if($this->request->has('member_id')){
            $member_id = $this->request->input('member_id');
            $PayMethod = new \App\Dao\PayMethod();
            $result = $PayMethod->getRealName($member_id);
            return $this->response->json($result);
        }else{
            return [
                'code'=>400,
                'message' => '参数错误',
            ];
        }
    }

    /**
     * User: wujiawei
     * DateTime: 2023/5/29 9:32
     * describe: 识别银行信息
     */
    #[PostMapping(path: "validateCard")]
    public function validateCard(){
        if($this->request->has('bank_card_number')){
            $bank_card_number = $this->request->input('bank_card_number');
            $PayMethod = new \App\Dao\PayMethod();
            $result = $PayMethod->validateCard($bank_card_number);
            return $result;
        }else{
            return [
                'code'=>400,
                'message' => '参数错误',
            ];
        }
    }

    /**
     * User: wujiawei
     * DateTime: 2023/5/29 11:01
     * describe: 绑定银行卡信息
     */
    #[PostMapping(path: "bindCard")]
    public function bindCard(){
        // 获取所有参数和文件
        $params = $this->request->all();
        // 同时判断多个值
        if ($this->request->has(['member_id', 'bank_card_number','account_name','opening_bank','bank_of_deposit'])) {
            //调用Dao层处理数据
            $PayMethod = new \App\Dao\PayMethod();
            $result = $PayMethod->bindCard($params);
        }else{
            $result = [
                'code'=>400,
                'message'=>'参数不完整'
            ];
        }
        return $this->response->json($result);
    }
}