<?php

namespace App\Http\Controllers\Util\UploadProductFromApi;

class TbUpload extends Base
{
    function main($data){
        $this->uploadProductToTb($data);
    }

    function uploadProductToTb($data){
        echo '淘宝';
        dd($data);
    }
}
