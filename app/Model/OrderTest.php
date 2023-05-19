<?php

declare(strict_types=1);

namespace App\Model;

use Hyperf\DbConnection\Model\Model;

/**
 * @property int $id 
 * @property string $orderCode 
 * @property int $status 
 * @property string $create_time 
 * @property string $update_time 
 */
class OrderTest extends Model
{
    /**
     * The table associated with the model.
     */
    protected ?string $table = 'order_test';

    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = [];

    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = ['id' => 'integer', 'status' => 'integer'];
}
