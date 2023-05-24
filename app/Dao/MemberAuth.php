<?php


namespace App\Dao;


use App\Model\CollectionTypeLog;
use App\Model\PersonInfo;
use App\Model\SpecialCert;
use App\Service\HttpRequestService;
use Hyperf\Context\ApplicationContext;
use Hyperf\DbConnection\Db;
use Hyperf\Di\Annotation\Inject;

class MemberAuth
{
    //依赖注入http请求类
    #[Inject]
    private HttpRequestService $httpRequestService;


    /**
     * User: wujiawei
     * DateTime: 2023/5/22 15:08
     * describe:检测身份证正反面功能
     * @param $checkData
     */
    public function checkIdcard($checkData,$type){
        switch ($type){
            case 'front':
                if (isset($checkData['words_result']['姓名'])) {
                    $idcard = $checkData['words_result']['公民身份号码']['words'];
                    $realname = $checkData['words_result']['姓名']['words'];
                    $duplicates = Db::table('person_info')->where('idcard',$idcard)->count();
                    if($duplicates>0){
                        return [
                            'code'=>'400',
                            'message'=>'此身份证已在平台认证'
                        ];
                    }else{
                        $idcardAge = $this->howOld($idcard);
                        if ($idcardAge < 16){
                            return [
                                'code' => '400',
                                'message' => '未年满16周岁,不可注册'
                            ];
                        }
                        if ($idcardAge > 80){
                            return [
                                'code' => '400',
                                'message' => '已超出80周岁,不可注册'
                            ];
                        }
                        return [
                            'code'=>200,
                            'realname'=>$realname,
                            'idcard'=>$idcard,
                        ];
                    }
                }else{
                    return [
                      'code'=>'400',
                      'message'=>'识别失败，请上传正确的身份证照片'
                    ];
                }
            case 'back':
                if (isset($checkData['words_result']['失效日期'])) {
                    return [
                        'code'=>200,
                        'lapsedate'=>$checkData['words_result']['失效日期']['words'],
                    ];
                }else{
                    return [
                        'code'=>'400',
                        'message'=>'识别失败，请上传正确的身份证照片'
                    ];
                }
        }
    }

    /**
     * User: wujiawei
     * DateTime: 2023/5/22 17:44
     * describe: 手持验证
     * @param $checkData
     * @return array|string[]
     */
    public function checkFace($checkData){
        if($checkData['error_code']==0){
            if($checkData['result']['face_num']==2){
                return [
                    'code'=>200,
                ];
            }else{
                return [
                    'code'=>'400',
                    'message'=>'识别失败，请上传清晰、正确的示例照片'
                ];
            }
        }else{
            return [
                'code'=>'400',
                'message'=>'识别失败，请上传清晰、正确的示例照片'
            ];
        }
    }

    /**
     * User: wujiawei
     * DateTime: 2023/5/23 14:01
     * describe: 实名认证审核
     * @param $data
     */
    public function examine($data){
        $lapseDate = $data['lapsedate'];
        $notday=date('Ymd',time());
        if($notday>$lapseDate){
            return [
                'code'=>'401',
                'message'=>'身份证不在有效期内'
            ];
        }
        $jdidcard = Db::table('person_info')
            ->where('idcard', $data['idcard'])
            ->value('id');
        if ($jdidcard) {
            return [
              'code'=>400,
              'message'=>'身份证号已被认证，请重新上传'
            ];
        }
        try {
            Db::beginTransaction();
            $frontimg = Db::table('img')->insertGetId(['source' => '2', 'url' => $data['fronturl']]);
            $backimg = Db::table('img')->insertGetId(['source' => '2', 'url' => $data['backurl']]);
            $holdimg = Db::table('img')->insertGetId(['source' => '2', 'url' => $data['holdurl']]);

            $personinfo_save = [
                'frontimg'   => $frontimg,
                'backimg'    => $backimg,
                'holdimg'    => $holdimg,
                'real_name'  => $data['realname'],
                'idcard'     => $data['idcard'],
                'create_time'=> time(),
            ];
            // 身份证号获取年龄及性别
            $personinfo_save['sex'] = $this->get_xingbie($data['idcard']);
            $personinfo_save['age'] = $this->howOld($data['idcard']);
            //修改person_info表
            Db::table('person_info')
                ->where('member_id', $data['member_id'])
                ->update($personinfo_save);
            //修改用户表状态
            Db::table('member')->where('id', $data['member_id'])->update(['person_status' => '1']);
            //特殊认证
            if($data['deformity'] || $data['military']){
                $specialData = [
                    'deformity'=>$data['deformity'] ? config('app.LGBFILE_URL').$data['deformity'] : '',
                    'military'=>$data['military'] ? config('app.LGBFILE_URL').$data['military'] : '',
                    'member_id'=>$data['member_id'],
                    'created_at'=>date('Y-m-d H:i:s',time())
                ];
                Db::table('special_cert')->insert($specialData);
            }
            Db::commit();
            //java修改群组相关信息
//          TODO::  $url = 'http://47.105.68.245:8088/v3/lyGroupMember/updateUser/'.$data['member_id'];
            $url = 'http://www.baidu.com';
            $this->httpRequestService->httpRequest($url,'GET');
            #推送消息到redis 用于生成头像
            $redis = ApplicationContext::getContainer()->get(\Hyperf\Redis\Redis::class);
            $redisData = [
                'member_id'=>$data['member_id'],
                'name'=>$data['realname']
            ];
            $redis->lPush('lgb:person:head',json_encode($redisData));
            //返回个人信息
            $memberReturn = Db::table('member')
                            ->select('person_status', 'person_tongyi')
                            ->where('id', $data['member_id'])
                            ->get();
            return [
                'code'=>200,
                'info'=>$memberReturn ? : null
            ];
        } catch (\Throwable $e) {
            Db::rollBack();
            return [
                'code'=>400,
                'message'=>'审核失败'
            ];
        }
    }
     //根据身份证号，自动返回性别
    public function get_xingbie($cid) {
        $sexint = (int)substr($cid,16,1);
        return $sexint % 2 === 0 ? '2' : '1';
    }

    /**
     *    计算身份证号当前周岁
     * @return $idcard 18位身份证号
     */
    private function howOld($idcard)
    {
        //过了这年的生日才算多了1周岁
        $date = strtotime(substr($idcard, 6, 8));//获得出生年月日的时间戳
        $today = strtotime('today');//获得今日的时间戳
        $diff = floor(($today - $date) / 86400 / 365);//得到两个日期相差的大体年数
        //strtotime加上这个年数后得到那日的时间戳后与今日的时间戳相比
        $age = strtotime(substr($idcard, 6, 8) . ' +' . $diff . 'years') > $today ? ($diff + 1) : $diff;
        return $age;
    }

    /**
     * User: wujiawei
     * DateTime: 2023/5/24 9:02
     * describe:已认证人员，信息获取
     */
    public function getAttestation($member_id){
        $result = [];
        //姓名身份证
        $person_info = PersonInfo::where('member_id', $member_id)->select('idcard', 'real_name')->first()->toArray();
        //是否选择收款方式
        $collection_type_num = CollectionTypeLog::query()->where('member_id', $member_id)->count('*');
        //特殊认证
        $special_cert = SpecialCert::query()->where('member_id', $member_id)->select('deformity','military')->first()->toArray();

        //处理数据
        $result['collection_type'] = ($collection_type_num > 0) ? 1 : 0;
        $result['special_cert'] = $special_cert;

        //姓名隐藏
        $result['realname'] = $this->substr_cut($person_info['real_name']);
        //银行卡隐藏
        $result['idcard'] = substr($person_info['idcard'], 0, 1) . str_repeat('*', strlen($person_info['idcard']) - 2) . substr($person_info['idcard'], -1);
        return $result;
    }
    private function substr_cut($user_name){
        $strlen     = mb_strlen($user_name, 'utf-8'); //获取字符长度
        $firstStr     = mb_substr($user_name,-1, 1, 'utf-8');  //查找最后一个
        $str= str_repeat('*', $strlen - 1).$firstStr;  //拼接最后一个+把字符串 "* " 重复 $strlen - 1 次：
        return $str;
    }
}