<?php

namespace App\Http\Controllers\Util\UploadProductFromApi;

class NbdUpload extends Base
{
    function main($data){
        $this->uploadProductToNbd($data);
    }

    function uploadProductToNbd($data){
        echo '新百大';
        dd($data);
    }
}
