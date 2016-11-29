<?php

namespace App\Http\Controllers\Util\SyncOrdersFromApi;

class StartSync extends Base
{
    public $api_types = [
        'taobao','tongcheng','newbd'
    ];

    /**
     * 触发订单同步操作入口(增量同步)
     *
     * @param $apiType
     * @return bool  调用成功
     * @throws \Exception 不支持的类型
     */
    function run($apiType)
    {
        if(!in_array($apiType,$this->api_types)){
            throw new \Exception('不支持的API类型');
        }
        $className = ucfirst($apiType);
        $instance = null;
        eval('$instance = new App\Http\Controllers\Util\SyncOrdersFromApi\\'.$className.'();');
        $instance->main();
    }
}