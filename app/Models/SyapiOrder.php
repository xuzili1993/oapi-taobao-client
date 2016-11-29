<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class SyapiOrder extends Model
{
    use SoftDeletes;

    protected $table = 'syapi_orders';

    public $timestamps = true;

//    指定批量赋值的不允许字段
    protected $guarded = ['id'];

    protected $dates = ['delete_at'];

    //根据手机号获取订单
    public function getOrderList($mobile)
    {
        return DB::table('syapi_orders')
            ->leftJoin('syapi_trades','syapi_trades.tid','=','syapi_orders.tid')
            ->where('syapi_trades.receiver_mobile',$mobile)->get();
    }
}
