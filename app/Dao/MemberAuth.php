<?php


namespace App\Dao;


use Hyperf\DbConnection\Db;

class MemberAuth
{
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
                    $lapseDate = $checkData['words_result']['失效日期']['words'];
                    $notday=date('Ymd',time());
                    if($notday>$lapseDate){
                        return [
                            'code'=>'400',
                            'message'=>'身份已过有效期，请重新上传'
                        ];
                    }else{
                        return [
                            'code'=>200,
                            'lapsedate'=>$checkData['words_result']['失效日期']['words'],
                        ];
                    }
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
            Db::commit();
            return [
              'code'=>200
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
}