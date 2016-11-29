<?php

namespace App\Http\Controllers\Util\SyncOrdersFromApi;

class Tongcheng extends Base
{
    function main(){
        $this->syncOrderCreated();
    }

    function syncOrderCreated(){

        echo ('tongcheng test');
    }
}