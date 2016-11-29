<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SyapiTrade extends Model
{
    use SoftDeletes;
    //指定表明
    protected $table = 'syapi_trades';

    public $timestamps = true;

//    指定批量赋值的允许字段
    protected $guarded = ['id'];

    protected $dates = ['delete_at'];

//      时间戳的变化
//    protected function getDateFormat()
//    {
//        return time();
//    }


}
