<?php

namespace App\Http\Controllers\Util\UploadProductFromApi;

class TcUpload extends Base
{
    function main($data){
        $this->uploadProductToTc($data);
    }

    function uploadProductToTc($data){
        echo '同程';
        dd($data);
    }
}
