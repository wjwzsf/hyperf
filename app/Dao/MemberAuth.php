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
                if($checkData['words_result']['姓名']){
                    $idcard = $checkData['words_result']['公民身份号码']['words'];
                    $duplicates = 0;
                    if($duplicates>0){
                        return [
                            'code'=>'400',
                            'message'=>'此身份证已在平台认证'
                        ];
                    }else{
                        return [
                            'code'=>200,
                            'realname'=>$checkData['words_result']['姓名']['words'],
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
                if($checkData['words_result']['失效日期']){
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
}