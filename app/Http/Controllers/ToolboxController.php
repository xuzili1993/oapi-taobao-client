<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Util\UploadProductFromApi\StartUpload;
use App\Http\Requests;
use App\Http\Controllers\Util\SyncOrdersFromApi\StartSync;

class ToolboxController extends Controller
{
    /**
     * 从api中获取数据
     *
     * @param $resource
     * @throws \Exception
     */
    function getDataFromApi($resource){
        // 同步淘宝订单
        (new StartSync())->run($resource);
    }

    /**
     * 产品上新
     *
     * @param Requests $requests
     * @throws \Exception
     */
    function uploadProduct(Requests $requests){
        $data = $requests->all();
        $upload = $requests->input('type');
        (new StartUpload())->upload($upload,$data);
    }


    /**
     * 测试淘宝消息订阅taobao
     */
    function getTestTaobaoApiEventSubscribe(){

    }

}
