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
                    'abbreviation'=>$result['bank']
                ]
            ];
        }else{
            $result = [
                'code'=>400,
                'message'=>'检测失败'
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
            //2.修改银行卡信息后需要重签协议，所以修改member表  save(array('person_status'=>0,'person_tongyi'=>3))
            Db::table('member')->where('id',$data['member_id'])->update(['person_status'=>0,'person_tongyi'=>3]);
            //3.银行卡信息变更记录 abbreviation 银行简称代码
            Db::table('paybind')->where(['member_id'=>$data['member_id'],'bind_type'=>0])->update(['is_del'=>1]);
            $cardPayData = array(
                'openid'        =>$cardNumber,
                'member_id'     =>$data['member_id'],
                'bind_type'     =>0,
                'createtime'    =>time(),
                'real_name'     =>$data['account_name'],
                'is_del'        =>0,
                'abbreviation'  =>$data['abbreviation'] ? : ''
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
            var_dump($e->getMessage());
            return [
                'code'=>400,
                'message'=>'审核失败'
            ];
        }
    }

}