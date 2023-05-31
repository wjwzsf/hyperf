<?php


namespace App\Dao;


use App\Service\HttpRequestService;
use Hyperf\DbConnection\Db;
use Hyperf\Di\Annotation\Inject;

class PayMethod
{
    //依赖注入http请求类
    #[Inject]
    private HttpRequestService $httpRequestService;

    /**
     * User: wujiawei
     * DateTime: 2023/5/29 9:34
     * describe:获取真实姓名
     * @param $member_id
     * @return array
     */
    public function getRealName($member_id){
        $real_name = Db::table('person_info')->where('member_id',$member_id)->value('real_name');
        return [
            'code'=>200,
            'message'=>'ok',
            'realname'=>$real_name
        ];
    }

    /**
     * User: wujiawei
     * DateTime: 2023/5/29 9:34
     * describe: 识别银行信息
     */
    public function validateCard($bank_card_number){
        $cardNumber = str_replace(' ', '', $bank_card_number); // 去除空格
        //检测银行卡是否唯一
        $cardCheck = $this->cardCheck($cardNumber);
        if($cardCheck['code']==400){
            return $cardCheck;
        }
        return $this->validateCardService($cardNumber);
    }

    /**
     * User: wujiawei
     * DateTime: 2023/5/30 14:19
     * describe:通过银行卡识别相关信息（公用）
     * @param $cardNumber
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function validateCardService($cardNumber){
        $url = "https://ccdcapi.alipay.com/validateAndCacheCardInfo.json";
        $param = [
            '_input_charset' => 'utf-8',
            'cardBinCheck' => 'true',
            'cardNo' => $cardNumber
        ];
        $result = $this->httpRequestService->httpRequest($url,'GET',$param);
        if($result['validated']===true){
            $bankname = Db::table('bank_card_base')->where('abbreviation',$result['bank'])->value('fullname');
            $result = [
                'code'=>200,
                'message'=>'ok',
                'info'=>[
                    'bankname'=>$bankname ? : '',
                    'abbreviation'=>$result['bank'],
                    'cardtype'=>$result['cardType']
                ]
            ];
        }else{
            $result = [
                'code'=>400,
                'message'=>'银行卡号错误,未能识别'
            ];
        }
        return $result;
    }
    /**
     * User: wujiawei
     * DateTime: 2023/5/29 13:56
     * describe: 检测银行卡是否唯一
     */
    public function cardCheck($bank_card_number){
        $cardNumber = str_replace(' ', '', $bank_card_number); // 去除空格
        $where[] = ['card', '=', $cardNumber];
        $cardCheck = Db::table('person_info')->where($where)->count('*');
        if($cardCheck>0){
            return [
              'code'=>400,
              'message'=>'银行卡已被绑定,请更换银行卡'
            ];
        }else{
            return [
                'code'=>200,
                'message'=>'ok'
            ];
        }
    }
    /**
     * User: wujiawei
     * DateTime: 2023/5/29 11:29
     * describe: 绑定银行卡信息
     */
    public function bindCard($data)
    {
        $cardNumber = str_replace(' ', '', $data['bank_card_number']); // 去除空格
        try {
            Db::beginTransaction();
            //1.修改person_info表的 card  opening_bank  bank_of_deposit  bankcard_change_count+1   account_name开户名   collection_type收款方式 默认为1  cardimg
            //修改修改person_info表且bankcard_change_count+1
            $person_info_update = [
                'card'=>$cardNumber,
                'opening_bank'=>$data['opening_bank'],
                'bank_of_deposit'=>$data['bank_of_deposit'],
                'account_name'=>$data['account_name'],
                'collection_type'=>1,
                'cardimg'=>$data['bankcardurl']
            ];
            Db::table('person_info')->where('member_id',$data['member_id'])->update($person_info_update);
            Db::table('person_info')->where('member_id',$data['member_id'])->increment('bankcard_change_count',1);
            //2.取消 修改银行卡信息后需要重签协议，所以修改member表  save(array('person_status'=>0,'person_tongyi'=>3))
            //Db::table('member')->where('id',$data['member_id'])->update(['person_status'=>0,'person_tongyi'=>3]);
            //3.银行卡信息变更记录 abbreviation 银行简称代码
            Db::table('paybind')->where(['member_id'=>$data['member_id'],'bind_type'=>0])->update(['is_del'=>1]);
            $cardPayData = array(
                'openid'        =>$cardNumber,
                'member_id'     =>$data['member_id'],
                'bind_type'     =>0,
                'createtime'    =>time(),
                'real_name'     =>$data['account_name'],
                'is_del'        =>0,
                'abbreviation'  =>$data['abbreviation'] ? : '',
                'cardtype'      =>$data['cardtype'] ? : ''
            );
            Db::table('paybind')->insert($cardPayData);
            //4.增加变更记录  collection_type_log
            $where_collection_type_log = [];
            $where_collection_type_log[] = ['member_id', '=', $data['member_id']];
            $where_collection_type_log[] = ['collection_type', '=', 1];
            $collection_type_log_id = Db::table('collection_type_log')->where($where_collection_type_log)->value('id');
            if($collection_type_log_id<=0){
                $collection_type_log_add = array(
                    'member_id'=>$data['member_id'],
                    'collection_type'=>1,
                    'update_time'=>time()
                );
                Db::table('collection_type_log')->insert($collection_type_log_add);
            }
            Db::commit();
            return [
                'code'=>200
            ];
        }catch (\Throwable $e) {
            Db::rollBack();
            return [
                'code'=>400,
                'message'=>'审核失败'
            ];
        }
    }

    /**
     * User: wujiawei
     * DateTime: 2023/5/30 14:10
     * describe:收款方式首页
     * @param $data
     * @return int[]
     */
    public function getPayMethodIndex($data){
        //查询card银行卡号
        $card = Db::table('person_info')->where('member_id',$data['member_id'])->value('card');
        if($card){
            $cardNumber = str_replace(' ', '', $card); // 去除空格
            //查询在paybind中是否存在
            $payBind = Db::table('paybind')->where(['member_id'=>$data['member_id'],'bind_type'=>0,'is_del'=>0])->first();
            if($payBind){
               if(empty($payBind->abbreviation)){
                   //没有存入的也需要增加一份新的记录
                   $this->CardLogPerfect($data['member_id'],$cardNumber);
               }
            }else{
                //paybind中不存在，需要增加记录
                $this->CardLogPerfect($data['member_id'],$cardNumber);
            }
        }else{
            return [
               'code'=>201,//未绑定
            ];
        }
        return [
          'code'=>200
        ];
    }
    //用于新增银行卡记录，旧用户没有logo等信息时
    private function CardLogPerfect($member_id,$cardNumber){
        $validateCardResult = $this->validateCardService($cardNumber);
        if($validateCardResult['code']==200){
            try {
                Db::beginTransaction();

                $abbreviation = $validateCardResult['info']['abbreviation'];
                $cardtype = $validateCardResult['info']['cardtype'];
                $cardPayData = array(
                    'openid' => $cardNumber,
                    'member_id' => $member_id,
                    'bind_type' => 0,
                    'createtime' => time(),
                    'is_del' => 0,
                    'abbreviation' => $abbreviation,
                    'cardtype'     =>$cardtype
                );
                //新增日志记录
                Db::table('paybind')->insert($cardPayData);
                //修改次数+1
                Db::table('person_info')->where('member_id', $member_id)->increment('bankcard_change_count', 1);
                Db::commit();
            }catch (\Throwable $e) {
                Db::rollBack();
            }
        }
        return true;
    }

    /**
     * User: wujiawei
     * DateTime: 2023/5/30 15:28
     * describe:银行卡已绑定页面
     * @param $data
     * @return array
     */
    public function getCardPage($data){
        //查询银行卡相关信息
        //查询在paybind中是否存在
        $cardData = (array)Db::table('paybind')
                    ->join('bank_card_base', 'paybind.abbreviation', '=', 'bank_card_base.abbreviation')
                    ->where([
                        'paybind.member_id' => $data['member_id'],
                        'paybind.bind_type' => 0,
                        'paybind.is_del' => 0,
                    ])
                    ->select(
                        'paybind.openid as cardnumber',
                        'bank_card_base.logo',
                        'bank_card_base.bankground',
                        'bank_card_base.fullname as bankname',
                        'paybind.cardtype'
                    )
                    ->first();
        //隐藏银行卡
        $len = strlen($cardData['cardnumber']);
        $suffix = substr($cardData['cardnumber'], -4); // 获取后四位
        $prefix = str_repeat('*', $len - 4); // 获取前 n - 4 位，用 * 号代替
        $cardData['cardnumber_hide'] = $prefix.$suffix;
        //查询手机号码
        $contact_tel = Db::table('member')->where('id',$data['member_id'])->value('contact_tel');
        $cardData['phone']=$contact_tel;
        return $cardData;
    }

    /**
     * User: wujiawei
     * DateTime: 2023/5/30 16:10
     * describe: 查看银行卡信息时--通过密码验证
     */
    public function pwdVerifyCard($data){
        $nowpassword = $this->sp_password($data['password']);
        $password = Db::table('member')->where('contact_tel',$data['phone'])->value('password');
        if($nowpassword == $password){
            return [
              'code'=>200
            ];
        }else{
            return [
                'code'=>400,
                'message'=>'密码错误，请重试'
            ];
        }
    }

    /**
     * User: wujiawei
     * DateTime: 2023/5/30 16:15
     * describe: 灵工邦app--密码生成
     * @param $pw
     * @return string
     */
    private function sp_password($pw){
        $authcode=env('LGB_AUTHCODE','XDejp9dwP6caQ7rC1q');
        return "###".md5(md5($authcode.$pw));
    }

    /**
     * User: wujiawei
     * DateTime: 2023/5/31 13:59
     * describe:保存优先付款方式
     * @param $data
     * @return array
     */
    public function saveCollectionType($data){
        $result = Db::table('person_info')->where('member_id',$data['member_id'])->update(['collection_type'=>$data['collection_type']]);
        if ($result === false) {
            // 更新失败
            return [
              'code'=>200,
              'message'=>'保存成功'
            ];
        } else {
            // 更新成功
            return [
                'code'=>400,
                'message'=>'保存失败'
            ];
        }
    }
}