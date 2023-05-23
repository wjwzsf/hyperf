<?php

declare(strict_types=1);

namespace App\Model;

use Hyperf\DbConnection\Model\Model;

/**
 */
class PersonInfo extends Model
{
    /**
     * The table associated with the model.
     */
    protected ?string $table = 'person_info';

    /**
     * @var bool 不自动管理 创建和修改
     */
    public bool $timestamps = false;

    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = [];

    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = [];
}
