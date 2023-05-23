<?php

declare(strict_types=1);

namespace App\Model;

use Hyperf\DbConnection\Model\Model;

/**
 * @property int $id 
 * @property string $url 
 * @property int $source 
 * @property int $opt_time 
 */
class Img extends Model
{
    /**
     * The table associated with the model.
     */
    protected ?string $table = 'Img';

    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = [];

    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = ['id' => 'integer', 'source' => 'integer', 'opt_time' => 'integer'];
}
