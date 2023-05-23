<?php

declare(strict_types=1);

namespace App\Model;

use Hyperf\DbConnection\Model\Model;

/**
 * @property int $id 
 * @property int $user_type 
 * @property int $person_status 
 * @property int $status 
 * @property string $name 
 * @property string $password 
 * @property string $contact_tel 
 * @property string $img1 
 * @property string $img2 
 * @property string $img3 
 * @property string $img4 
 * @property string $openid 
 * @property int $login_status 
 * @property int $tongyi 
 * @property int $tongyi_time 
 * @property int $person_tongyi 
 * @property int $call 
 * @property int $create_time 
 * @property int $isactive 
 * @property string $register_ip 
 * @property string $login_ip 
 * @property int $wxuser_id 
 * @property int $level 
 * @property string $is_channel 
 * @property string $channel_type 
 * @property string $device_id 
 * @property string $img5 
 * @property string $qword 
 * @property int $user_id 
 * @property int $is_argument 
 * @property int $auth_type 
 * @property int $follow_num 
 * @property int $approval_num 
 * @property string $imaccount 
 * @property int $is_authentication 
 * @property int $auto_confirm 
 * @property int $syncflag 
 * @property int $is_bank 
 * @property int $tax_collection 
 * @property int $is_platformjob 
 * @property int $order_id 
 * @property int $source 
 */
class Member extends Model
{
    /**
     * @var bool 不自动管理 创建和修改
     */
    public bool $timestamps = false;
    /**
     * The table associated with the model.
     */
    protected ?string $table = 'member';

    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = [];

    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = ['id' => 'integer', 'user_type' => 'integer', 'person_status' => 'integer', 'status' => 'integer', 'login_status' => 'integer', 'tongyi' => 'integer', 'tongyi_time' => 'integer', 'person_tongyi' => 'integer', 'call' => 'integer', 'create_time' => 'integer', 'isactive' => 'integer', 'wxuser_id' => 'integer', 'level' => 'integer', 'user_id' => 'integer', 'is_argument' => 'integer', 'auth_type' => 'integer', 'follow_num' => 'integer', 'approval_num' => 'integer', 'is_authentication' => 'integer', 'auto_confirm' => 'integer', 'syncflag' => 'integer', 'is_bank' => 'integer', 'tax_collection' => 'integer', 'is_platformjob' => 'integer', 'order_id' => 'integer', 'source' => 'integer'];



}
