<?php
return [
    'app_key'        =>  '23492876',
    'app_secret'     =>  'e495e586a16246157b1cd855285ea4c4',
    'format'         =>  'json',
    'fieldTypes'     =>  "tid,payment,receiver_name,receiver_state,receiver_zip,shipping_type,receiver_phone,receiver_mobile,received_payment,status,price,buyer_rate,orders,created,pay_time,modified,end_time",
    'sessionKey'     =>  '6202729b82925bdb4f8dd27c9cef90448904ace3c27d9ce1911947373',
    'orderStatus'    =>  'WAIT_SELLER_SEND_GOODS',
    'gatewayUrl'     =>  env('API_GATEWAY_TAOBAO','http://gw.api.taobao.com/router/rest'),
    'daySecond'      =>  '86400',
];