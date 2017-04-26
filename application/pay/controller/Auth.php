<?php
namespace app\pay\controller;

use think\Session;
use app\pay\wechat\Wechat;
use app\pay\wechat\Wechatpay;

use app\pay\service\PayService;

class Auth
{
    /**
        商户号：1404056502
        支付密钥：loveme02086886088loveme020868860

        AppID(应用ID)wx8c4d0fba3ac9acbb
        AppSecret：fe478ecc4823cf6ac2712bdab2494dfb
        URL=http://loveme.xifenhezi.com/message
        Token：loveme02086886088loveme020868860

        EncodingAESKey：y8InkrDtseWKyK6XTWDwsEYyrUopuRuzURAl5fSWABT
    */
    const BASEURL           = "http://loveme.xifenhezi.com";
    const WX_TOKEN          = "loveme02086886088loveme020868860";
    const WX_ENCODINGAESKEY = "y8InkrDtseWKyK6XTWDwsEYyrUopuRuzURAl5fSWABT";
    const WX_APPID          = "wx8c4d0fba3ac9acbb";
    const WX_APPSECRET      = "fe478ecc4823cf6ac2712bdab2494dfb";

    public $open_id;
	public $wxuser;

    ///index.php?g=User&m=Wechat_behavior&a=statisticsOfSingleFans&openid=oL1NjwdVXbcVMovLUTNe7Exu25e4
    public function index()
    {
        return json(['ret'=>0]);
    }

    public function wechat(){
        Session::set('wechat', 'start');
        $url = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        $scope = 'snsapi_base';
        $code  = input('get.code');
        $token_time = input('session.token_time/d', 0);
        $user_token = input('session.user_token/s');
        $open_id    = input('session.open_id/s');
        $wxuser = null;
		if(!$code && isset($token_time) && isset($user_token) && $token_time>time()-3600)
		{
            //return view('error_tpl', ['idx'=>1, 'error'=>'微信授权已登录']);
            return redirect('/rooms');
		}else{
			$wechat = new Wechat(array(
                'token'         =>  config('wx_token'), //填写你设定的key
                'encodingaeskey'=>  config('wx_encodingasekey'), //填写加密用的EncodingAESKey，如接口为明文模式可忽略
                'appid'         =>  config('wx_appid'), //填写高级调用功能的app id
                'appsecret'     =>  config('wx_appsecret'), //填写高级调用功能的密钥
                'agentid'=>'1', //应用的id
                'debug'=>true, //调试开关
                '_logcallback'=>'logg', //调试输出方法，需要有一个string类型的参数
            ));
            //$wechat->valid();
			if ($code) {
				$json = $wechat->getOauthAccessToken();
                //{access_token, expires_in, refresh_token, openid, scope}
				if (!$json) {
					return view('error_tpl', ['idx'=>1, 'error'=>'获取用户授权失败']);
				}
                $open_id = $json["openid"];
                $access_token = $json['access_token'];

                Session::set('open_id', $open_id);
                Session::set('user_token', $access_token);
                Session::set('token_time', time());
				$userinfo = $wechat->getUserInfo($open_id);
				if ($userinfo && !empty($userinfo['nickname'])) {//
					$wxuser = array(
							'open_id'=>$open_id,
							'nickname'=>$userinfo['nickname'],
							'sex'=>intval($userinfo['sex']),
							'location'=>$userinfo['province'].'-'.$userinfo['city'],
							'avatar'=>$userinfo['headimgurl']
					);
                    Session::set('wxuser', $wxuser);
				} elseif (strstr($json['scope'], 'snsapi_userinfo') !== false) {//
					$userinfo = $wechat->getOauthUserinfo($access_token, $open_id);
					if ($userinfo && !empty($userinfo['nickname'])) {
						$wxuser = array(
								'open_id'=>$open_id,
								'nickname'=>$userinfo['nickname'],
								'sex'=>intval($userinfo['sex']),
								'location'=>$userinfo['province'].'-'.$userinfo['city'],
								'avatar'=>$userinfo['headimgurl']
						);
                        Session::set('wxuser', $wxuser);
					} else {
						return view('error_tpl', ['idx'=>2, 'error'=>'获取用户信息失败']);
					}
				}
				if ($wxuser) {
                    $redirect_url = Session::get('red_url')?Session::get('red_url'):'/';
                    return redirect($redirect_url);
				}
				$scope = 'snsapi_userinfo';
			}
		}
        $oauth_url = $wechat->getOauthRedirect($url, "wxbase", $scope);
        if(!isset($oauth_url) || empty($oauth_url)) $oauth_url = 'null';
        //return view('hello', ['idx'=>4, 'oauth_url'=>$oauth_url, 'code'=>$code]);
        return redirect($oauth_url);
    }

}
