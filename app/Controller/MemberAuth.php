<?php


namespace App\Controller;


use App\Service\TextRecognitionService;
use App\Service\UploadServer;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpMessage\Upload\Input;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Annotation\PostMapping;

#[Controller(prefix: "memberAuth")]
class MemberAuth extends AbstractController
{
    #[Inject]
    private TextRecognitionService $recognitionService;//ocr识别服务 通过依赖注入服务
    #[Inject]
    private UploadServer $uploadServer;//上传图片服务

    /**
     * 识别身份证正反面
     */
    #[PostMapping(path: "idcard")]
    public function idcard(){
        // 获取所有参数和文件
        $params = $this->request->all();
        // 同时判断多个值
        if (!$this->request->has(['type'])) {
            return [
                'code'=>400,
                'message' => '参数错误',
            ];
        }
        if ($this->request->hasFile("{$params['type']}")) {
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
        }else{
            return [
                'code'=>400,
                'message' => '请上传图片',
            ];
        }
    }

    /**
     * User: wujiawei
     * DateTime: 2023/5/23 11:14
     * describe: 手持身份证验证
     * @return array|int[]|\Psr\Http\Message\ResponseInterface|string[]
     */
    #[PostMapping(path: "holdidcard")]
    public function holdidcard(){
        // 获取所有参数和文件
        $params = $this->request->all();
        if ($this->request->hasFile('hold')) {
            //size name 上传到oss的文件路径
            $imgReturn = $this->uploadServer->uploadImage(['size'=>'4096','name'=>'hold','ossurl'=>'Upload/V3/idcard/'.date('Y-m-d',time())]);
            switch ($imgReturn['code']){
                case 200:
                    //识别功能
                    $data=[
                        'localurl'=>$imgReturn['localurl']
                    ];
                    $checkData = $this->recognitionService->faceCheck($data);
                    //检测手持
                    $memberAuth = new \App\Dao\MemberAuth();
                    $result = $memberAuth->checkFace($checkData);
                    if ($result['code']==200){
                        //设置返回的地址
                        $result['holdurl']=$imgReturn['url'];
                    }
                    @unlink(BASE_PATH . $imgReturn['localurl']);
                    return $result;
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
     * DateTime: 2023/5/23 11:33
     * describe: 特殊认证接口
     * @return array|\Psr\Http\Message\ResponseInterface
     */
    #[PostMapping(path: "special")]
    public function special(){
        // 获取所有参数和文件
        $params = $this->request->all();
        if ($this->request->hasFile('special')) {
            //size name 上传到oss的文件路径
            $imgReturn = $this->uploadServer->uploadImage(['size'=>'4096','name'=>'special','ossurl'=>'Upload/V3/special/'.date('Y-m-d',time())]);
            switch ($imgReturn['code']){
                case 200:
                    //设置返回地址
                    $result['specialurl']=$imgReturn['url'];
                    @unlink(BASE_PATH . $imgReturn['localurl']);
                    return $result;
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

    /**
     * User: wujiawei
     * DateTime: 2023/5/23 13:58
     * describe: 实名认证审核
     */
    #[PostMapping(path: "examine")]
    public function examine(){
        // 获取所有参数和文件
        $params = $this->request->all();
        // 同时判断多个值
        if ($this->request->has(['member_id', 'idcard','fronturl','backurl','holdurl','realname','lapsedate'])) {
            //调用Dao层处理数据
            $memberAuth = new \App\Dao\MemberAuth();
            $result = $memberAuth->examine($params);
        }else{
            $result = [
              'code'=>400,
              'message'=>'参数错误'
            ];
        }
        return $this->response->json($result);
    }

    /**
     * User: wujiawei
     * DateTime: 2023/5/24 8:37
     * describe: 已认证人员，信息展示
     */
    #[GetMapping(path: "attshow")]
    public function attestationShow(){
        //获取参数
        $member_id = $this->request->input('member_id',null);
        //调用Dao层处理数据
        $memberAuth = new \App\Dao\MemberAuth();
        $result = $memberAuth->getAttestation($member_id);
        return $this->response->json($result);
    }

    /**
     * User: wujiawei
     * DateTime: 2023/5/24 13:52
     * describe:单独上传特殊认证
     * @return array|\Psr\Http\Message\ResponseInterface
     */
    #[PostMapping(path: "upspecial")]
    public function upspecial(){
        // 获取所有参数和文件
        $params = $this->request->all();
        if(!$this->request->has('member_id')){
            return [
                'code'=>400,
                'message' => '参数错误',
            ];
        }
        if ($this->request->hasFile('special')) {
            //size name 上传到oss的文件路径
            $imgReturn = $this->uploadServer->uploadImage(['size'=>'4096','name'=>'special','ossurl'=>'Upload/V3/special/'.date('Y-m-d',time())]);
            switch ($imgReturn['code']){
                case 200:
                    //修改调用
                    $memberAuth = new \App\Dao\MemberAuth();
                    $result = $memberAuth->upspecial($imgReturn['url'],$params);
                    @unlink(BASE_PATH . $imgReturn['localurl']);
                    return $result;
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