<?php

namespace App\Http\Controllers\Util\SyncOrdersFromApi;

use Illuminate\Support\Facades\Log;
use App\Models\SyapiOrder;                      //产品状态更新
use TopClient;
use TopClient\request\TradesSoldIncrementGetRequest;
class Taobao extends Base
{
    //修改淘宝的订单状态（展示给用户）
    const STATUS_MAPPING = [
        'WAIT_SELLER_SEND_GOODS'    =>  '待发货（已付款）',
        'WAIT_BUYER_PAY'            =>  '等待卖家付款',
        'WAIT_BUYER_CONFIRM_GOODS'  =>  '等待买家确认收货',
        'TRADE_FINISHED'            =>  '交易完成',
        'TRADE_CLOSED_BY_TAOBAO'    =>  '交易关闭（付款前）',
        'TRADE_CLOSED'              =>  '交易关闭'
    ];

    // 消息订阅获取这些状态变化的订单
    public $status_type=[
        "TRADE_FINISHED",
        "WAIT_BUYER_CONFIRM_GOODS",
        "TRADE_CLOSED"
    ];

    protected $client;

    protected $sessionKey;

    protected $endCreated;

    protected $startCreated;

    protected $pageSize;                    // 页码数量

    protected $fieldTypes;                  // 响应参数

    protected $orderStatus;                 // 订单状态

    protected $orderSyncSeconds = 120;      // 订单同步间隔(单位：秒)

    function __construct()
    {
        $this->client = TopClient::connection();
        $this->client->gatewayUrl = config('taobao.gatewayUrl');
        $this->client->appkey = config('taobao.app_key');
        $this->client->secretKey = config('taobao.app_secret');
        $this->client->format = config('taobao.format');
        $this->fieldTypes = config('taobao.fieldTypes');
        $this->sessionKey = config('taobao.sessionKey');
        $this->orderStatus = config ('taobao.orderStatus');
        $this->endCreated = date('Y-m-d H:i:s',time());        //mktime (时，分，秒，月，日，年)；mktime(12,0,0,11,14,2016)
        $this->startCreated = date('Y-m-d H:i:s',(strtotime($this->endCreated) - $this->orderSyncSeconds));
        $this->pageSize = 10;
    }

    function main()
    {
        $this->syncTradesCreated();
    }

    /**
     *
     * 同步淘宝订单的创建
     *
     * @param int $pageNo
     * @return int 返回同步完成的页数
     * @throws \Exception   api接口错误时
     */
    function syncTradesCreated($pageNo = 1)
    {
        $req = new TradesSoldIncrementGetRequest;
        $req->setFields($this->fieldTypes);
        $req->setStartModified($this->startCreated);
        $req->setEndModified($this->endCreated);
        $req->setStatus($this->orderStatus);                    // 状态为已付款，获取更过有意义的订单
        $req->setPageNo((string)($pageNo));                     // 页码的格式是字符串 需转换为字符串
        $req->setPageSize($this->pageSize);
        $req->setUseHasNext("true");
        $resp = $this->client->execute($req, $this->sessionKey);
        if (!empty($resp->code)) {                              // 同步失败 抛出子命令信息
            if($resp->code == 27){
                throw new \Exception('sessionkey过期');
            }
            throw new \Exception($resp->code);
        }
        if (isset($resp->trades)) {
            $this->syncTradeFormat($resp,$this->startCreated,$this->endCreated);
        } else {                    //没有数据时直接填入日志中
            (new Base())->insetCreatedLogsToDB('taobao', $req->getStartModified(), $req->getEndModified(), 0, 0);
        }
        if ($resp->has_next) {
            $this->syncTradesCreated($pageNo + 1);
        }
    }

    /**
     *
     * 格式数据及插入数据库
     *
     * @param $resp
     * @param $startCreated
     * @param $endCreated
     * @return bool
     *
     */
    function syncTradeFormat($resp, $startCreated, $endCreated) {
        $formattedTradeData = $this->formatTradeData($resp, $startCreated, $endCreated);      //对数组进行格式化，形成规范的数组
        if ($formattedTradeData) {
            (new Base())->saveTradesToDB($formattedTradeData);
        }
    }

    /**
     * 格式化订单数据
     *
     * @param $inputData
     * @return array
     */
    function formatTradeData($inputData,$startCreated,$endCreated)
    {
        if (isset($inputData->trades)) {
            $outputArr = \GuzzleHttp\json_decode(json_encode($inputData->trades),true);
            $outputArr = array_merge($outputArr,[
                'startCreated'   =>  $startCreated,
                'endCreated'     =>  $endCreated,
            ]);
            return $outputArr;
        } else {
            Log::debug('没有订单');
            return false;
        }
    }
    /**
     * 同步订单的增量修改
     *
     * @return bool
     */

    function syncOrderUpdate($content){
        $orderContent = str_replace(array(":",","), array('"=>"','","'),'array("'.$content.'")');       // 字符串转换为数组
        eval("\$orderContent"." = $orderContent;");             // 把字符串作为php代码执行(固定格式)
        Log::debug($content.'+'.$this->endCreated);              // 订单推送日志
        print_r($orderContent);
        if (isset($orderContent['status'])) {
            // 消息推送会按照交易流程一个一个推送，所以一个我们需要调用的交易流程会经历待发货状态；
            // 所以，如果有待发货状态的话那就调用下接口，获取这时间的数据
            if ($orderContent['status'] == config ('taobao.orderStatus')) {
                $this->main();
            }
            if (in_array($orderContent['status'],$this->status_type)) {
                echo 'true',
                SyapiOrder::where('oid',$orderContent['oid'])->update([
                    'status' => $orderContent['status'],
                ]);
            }
        }
    }
}