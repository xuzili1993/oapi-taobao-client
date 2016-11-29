<?php

namespace App\Http\Controllers\Util\SyncOrdersFromApi;

use App\Models\SyapiCreatedLog;
use App\Models\SyapiOrder;
use App\Models\SyapiTrade;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
class Base
{
    protected $logTimeSpacing;

    protected $tradesTimeSpacing;

    public function __construct()
    {
        $this->logTimeSpacing = config('taobao.daySecond') * 7;         // 一周前
        $this->tradesTimeSpacing = config('taobao.daySecond') * 180;    // 半年前
    }

    /**
     * 保存订单到数据库
     *
     * @param $trades
     * @return boolt
     * @throws \Exception
     */
    function saveTradesToDB($trades)
    {
        $orderCount = 0;                                     //初始化总产品数量值
        foreach($trades['trade'] as $trade){
            $v = Validator::make($trade, [
                'tid'               => 'required',
                'payment'           => 'required',
                'received_payment'  => 'required',
                'status'            => 'required',
                'created'           => 'required',
//                'receiver_mobile'   => 'required',       // 通过收货人电话来判定用户
            ]);
            $orderCount += count($trade['orders']['order']);                    //累加每次订单下的产品数量值
            if ($v->fails()) {
                Log::debug('trades验证错误');
                throw new \Exception('格式错误');
            }
            try {       // 事务
                DB::beginTransaction();
                    $this->insertTradesToDB($trade);
                    $this->saveOrdersToDB($trade['tid'],$trade['orders']);
                DB::commit();
            } catch (\Exception $e) {
                Log::error($e->getMessage());
                DB::rollBack();
            }
        }
        $this->writeTaobaoDBLog($trades, $orderCount, 'taobao');
        echo count($trades['trade']).'<br>';
    }


    /**
     * 保存产品数据
     *
     * @param $tid
     * @param $orders
     * @return static
     * @throws \Exception
     */

    function saveOrdersToDB($tid, $orders)
    {
        foreach($orders['order'] as $order)
        {
            $order = array_merge($order,[
                'tid'   =>  $tid
            ]);
            $v = Validator::make($order, [
                'num'       => 'required',
                'oid'       => 'required | numeric',
                'tid'       => 'required | numeric',
                'title'     => 'required',
                'status'    => 'required',
                'price'     => 'required',
                'total_fee' => 'required',
            ]);
            if ($v->fails()) {
                Log::debug('orders验证错误');
                throw new \Exception('格式错误');
            } else {
                //添加数据到产品表
                $this->insertOrdersToDB($order);
            }
        }
    }

    /**
     * 写API日志(current为'1'代表数据是最新的 )
     *
     * @param $trades
     * @param $orderCount
     * @return bool
     */
    function writeTaobaoDBLog($trades,$orderCount,$platFrom)
    {
        $this->insetCreatedLogsToDB($platFrom, $trades['startCreated'], $trades['endCreated'], count($trades['trade']), $orderCount);
        Log::debug('Base.php/获取api数据及插入数据库成功');
        return true;
    }

    /**
     *
     * 订单数据插入数据库
     *
     * @param $trade
     */
    function insertTradesToDB($trade) {
        SyapiTrade::firstOrCreate([
            'tid'               => isset($trade['tid'])  ? $trade['tid'] : null,
            'receiver_state'    => isset($trade['receiver_state']) ? $trade['receiver_state'] : null,
            'receiver_zip'      => isset($trade['receiver_zip']) ? $trade['receiver_zip'] : null,
            'received_payment'  => isset($trade['received_payment']) ? $trade['received_payment'] : null,
            'created'           => isset($trade['created']) ? $trade['created'] : null,
            'modified'          => isset($trade['modified']) ? $trade['modified'] : null,
            'pay_time'          => isset($trade['pay_time']) ? $trade['pay_time'] : null,
            'receiver_name'     => isset($trade['receiver_name']) ? $trade['receiver_name'] : null,
            'receiver_phone'    => isset($trade['receiver_phone']) ? $trade['receiver_phone'] : null,
            'receiver_mobile'   => isset($trade['receiver_mobile']) ? $trade['receiver_mobile'] : null,
            'buyer_rate'        => isset($trade['buyer_rate']) ? $trade['buyer_rate'] : null,
        ]);
    }

    /**
     *
     * 产品数据插入到数据库
     *
     * @param $order
     *
     */
    function insertOrdersToDB($order) {
        SyapiOrder::firstOrCreate([
            'oid'                   => isset($order['oid']) ? $order['oid'] : null,
            'outer_iid'             => isset($order['outer_iid']) ? $order['outer_iid'] : null,
            'tid'                   => isset($order['tid']) ? $order['tid'] : null,
            'item_meal_name'        => isset($order['item_meal_name']) ? $order['item_meal_name'] : null,
            'pic_path'              => isset($order['pic_path']) ? $order['pic_path'] : null,
            'refund_status'         => isset($order['refund_status']) ? $order['refund_status'] : null,
            'snapshot_url'          => isset($order['snapshot_url']) ? $order['snapshot_url'] : null,
            'status'                => isset($order['status']) ? $order['status'] : null,
            'title'                 => isset($order['title']) ? $order['title'] : null,
            'num'                   => isset($order['num']) ? $order['num'] : null,
            'order_from'            => '淘宝',
            'payment'               => isset($order['payment']) ? $order['payment'] : null,
            'discount_fee'          => isset($order['discount_fee']) ? $order['discount_fee'] : null,
            'sku_id'                => isset($order['sku_id']) ? $order['sku_id'] : null,
            'sku_properties_name'   => isset($order['sku_properties_name']) ? $order['sku_properties_name'] : null,
        ]);
    }

    /**
     *
     * 日志数据库的插入
     *
     * @param $platFrom     “来源”
     * @param $start        “订单开始时间”
     * @param $end          “订单结束时间”
     * @param $count        “订单数量”
     * @param $orderCount   “产品数量”
     */
    function insetCreatedLogsToDB($platFrom, $start, $end, $count, $orderCount){
        $log = SyapiCreatedLog::where('current',1)->get();                 // 适用于第一笔订单时
        $logNum = count($log);                                             // 判断是否存在
        if ($logNum == 1) {
            SyapiCreatedLog::where('current','1')->update(['current' => '0']);      // 把前面数据的‘1’更新为‘0’
        }
        SyapiCreatedLog::create([
            'plat_from'         => $platFrom,
            't_start_created'   => $start,                         //  增量订单的开始时间
            't_end_created'     => $end,                           //  增量订单的结束时间
            'trades_count'      => $count,                         //  增量订单时间段的交易数量
            'orders_count'      => $orderCount,                    //  增量订单时间段的产品数量
            'created_at'        => date('Y-m-d H:i:s',time()),     //  获取订单的时间
            'current'           => '1'                             //  ‘1’代表最新一次更新 其他时刻为‘0’
        ]);
    }

    /**
     * 把时间戳转换为日期
     *
     * @param $timestamp ‘时间戳’
     * @return string|‘标准时间格式’
     */
    function timestampToDate($timestamp){
        return date('Y-m-d H:i:s',$timestamp);
    }

    /**
     * 删除七天前的更新日志（回滚）
     *
     */
    function SyncTradesLogsDelete(){
        $nowTime = $this->timestampToDate(time());
        $deleteAt = $this->timestampToDate(strtotime($nowTime) - $this->logTimeSpacing);    //7天前
        SyapiCreatedLog::where('created_at', '<', $deleteAt)->delete();
    }

    /**
     * 删除半年前的订单(回滚)
     *
     */
    function SyncTradesDelete(){
        $nowTime = $this->timestampToDate(time());
        $deleteAt = $this->timestampToDate(strtotime($nowTime) - $this->tradesTimeSpacing);     //6个月前
        SyapiTrade::where('created_at', '<', $deleteAt)->forceDelete();        //因为软删除
        SyapiOrder::where('created_at', '<', $deleteAt)->forceDelete();
    }
}