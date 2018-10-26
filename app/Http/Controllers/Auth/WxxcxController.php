<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Iwanli\Wxxcx\Wxxcx;
use Illuminate\Support\Facades\Validator;

class WxxcxController extends Controller
{
    protected $wxxcx;

    function __construct(Wxxcx $wxxcx)
    {
        $this->wxxcx = $wxxcx;
    }

    public function validator($data)
    {
        $message = [
            'required'  => '数据不能为空',
            'numeric'   => '数据不合法'
        ];
        return Validator::make($data, [
            'code'          => 'required|numeric',
            'encryptedData' => 'required',
            'iv'            => 'required'
        ]);
    }

    /**
     * 小程序登录获取用户信息
     * @author 晚黎
     * @date   2017-05-27T14:37:08+0800
     * @return [type]                   [description]
     */
    public function getWxUserInfo(Request $request)
    {
        if ($request->isMethod('post')) {
            $validator = $this->validator($request->all());
            if ($validator->fails()) {
                $errors = $validator->errors->first();
                if ($errors == '数据不能空') {
                    $code = 1001;
                } else if ($errors == '数据不合法') {
                    $code = 1002;
                } else {
                    $code = 5000;
                }
                return response()->json([
                    'code'  => $code,
                    'msg'   => $errors
                ]);
            }
            $code = request('code', '');
            $encryptedData = request('encryptedData', '');  // encryptedData 和 iv 在小程序端使用 wx.getUserInfo 获取
            $iv = request('iv', '');

            $userInfo = $this->wxxcx->getLoginInfo($code);//根据 code 获取用户 session_key 等信息, 返回用户openid 和 session_key
            $openid = $userInfo['openid'] ? $userInfo['openid'] : '';

            //获取解密后的用户信息
            return $this->wxxcx->getUserInfo($encryptedData, $iv);
        }
    }
}