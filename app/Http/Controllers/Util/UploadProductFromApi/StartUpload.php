<?php

namespace App\Http\Controllers\Util\UploadProductFromApi;


class StartUpload extends Base
{
    public $upload_type = [
        'tbUpload','tcUpload','nbdUpload'
    ];

    public function upload($uploadType,$data){
        if(!in_array($uploadType, $this->upload_type)){
            throw new \Exception('上传的不支持此api');
        }
        $className = ucfirst($uploadType);
        $instance = null;
        eval('$instance = new App\Http\Controllers\Util\UploadProductFromApi\\'.$className.'();');
        $instance->main($data);
    }
}
