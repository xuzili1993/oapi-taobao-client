<?php
namespace App\Http\Controllers\Util\SyncOrdersFromApi;

class Newbd extends Base
{
    function main(){
//        SyapiTrade::find('301')->delete();           //软删除(需要模型里面use SoftDeletes)
//        $post=SyapiTrade::onlyTrashed()->where('id',301)->get();     //获取软删除的
//        dd($post);
//        SyapiTrade::withTrashed()->where('id',301)->restore();         //取消软删除
//        $post->restore();
        echo ('newbd test');
    }
}