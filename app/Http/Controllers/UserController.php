<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Util\SmsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

include_once '../app/libs/aliyun-php-sdk-core/Config.php';
use App\Http\Requests;

use Gregwar\Captcha\CaptchaBuilder;
use Illuminate\Support\Facades\Session;
class UserController extends Controller
{
    public function code(Request $request){
        $phone=$request->input('phone');
        $code=mt_rand(100000,999999);
            $sendCode=(new SmsController())->sendVerifyCode($phone,$code);
            if($sendCode){
                return response()->json(['errCode'=>0]);
            }else{
                return response()->json(['errCode'=>1]);
            }
//        }

    }
    public function confirmCode(Request $request){
        $phone=$request->input('phone');
        $code=$request->input('code');
        $confirmCode=(new SmsController())->confirmVerifyCode($phone,$code);
        if($confirmCode){
            return response()->json(['errCode'=>0]);
        }else{
            return response()->json(['errCode'=>1]);
        }
    }
    public function loginCode(Request $request){
        $builder = new CaptchaBuilder;
        //可以设置图片宽高及字体
        $builder->build($width = 100, $height = 40, $font = null);
        //获取验证码的内容
        $phrase = $builder->getPhrase();
        //把内容存入session
        session(['loginCode'=> $phrase]);
        //生成图片
        header("Cache-Control: no-cache, must-revalidate");
        header('Content-Type: image/jpeg');
        $builder->output();
    }

    public function loginConfirmCode(Request $request){
        $loginCode=$request->input('loginCode');
        $code = session('loginCode');
        if($code == $loginCode){
            return response()->json(['errCode'=>0]);
        }else{
            return response()->json(['errCode'=>1]);
        }
    }

    public function reset(){
        return view('auth.passwords.tel');
    }
    public function ResetPasswordTel(Request $request){
        $resetTel=$request->input('resetTel');
        $resetPassword=bcrypt($request->input('resetPassword'));
        $reset=DB::table('users')->where('tel',$resetTel)->update(['password'=>$resetPassword]);
        if($reset){
            return response()->json(['errCode'=>0]);
        }else{
            return response()->json(['errCode'=>1]);
        }
    }
}
