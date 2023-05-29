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


    }
}