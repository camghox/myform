<?php
namespace app\pay\controller;

use think\Request;
use think\Session;
use think\Log;

use app\pay\wechat\Wechat;
use app\pay\wechat\ErrorCode;
use app\pay\wechat\Wechatpay;

use app\pay\service\PayService;

class Index
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

    public $open_id;
	public $wxuser;

    public function index()
    {
        return json(['ret'=>'0']);
    }

    /*
        公众号支付
    */
    public function mppay()
    {
        $open_id = Session::get('open_id');
        if(!isset($open_id) || empty($open_id)){
            return redirect('/wechat');
        }
        $trade_no = input('trade_no', Session::get('trade_no'));
        $PayService = new PayService;
        $trade = $PayService->get($trade_no);

        Log::record("open_id=$open_id");
        Log::record("trade_no=$trade_no");

        $options = array(
                'token'         =>  config('wx_token'), //填写你设定的key
                'encodingaeskey'=>  config('wx_encodingasekey'), //填写加密用的EncodingAESKey，如接口为明文模式可忽略
                'appid'         =>  config('wx_appid'), //填写高级调用功能的app id
                'appsecret'     =>  config('wx_appsecret') //填写高级调用功能的密钥
        );
        $wechat = new Wechat($options);
        $notify_url = 'http://'.$_SERVER['HTTP_HOST'].config('wxpay_notifyurl');
        $url = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER["REQUEST_URI"];
        $jssign = $wechat->getJsSign($url);
        Log::record('jssign='.json_encode($jssign));
        //{"appId":"wx3f408e2766a56176","nonceStr":"VaRyMvFQAldPDps0","timestamp":1486470325,"url":"http:\/\/pay.xifenhezi.com\/pay\/?openid=oL1NjwdVXbcVMovLUTNe7Exu25e4","signature":"8a86a2a1e2dbf505673402b3fecda988c6b537b9"}

        $wechatpay = new Wechatpay(array(
                    'appid'     => config('wx_appid'),
                    'secret'    => config('wx_appsecret'),
                    'pem'       => config('wxpay_pem'),
                    'mch_id'    => config('wxpay_mchid'),
                    'payKey'    => config('wxpay_paykey'),
        ));
        $paysign = $wechatpay->unifiedOrder(
                    $open_id, 
                    $body = "房间预约：".$trade['content'], 
                    $sn = $trade_no, 
                    $money = 0.01, 
                    $notify_url,
                    $extend = array()
        );

        Log::record('wechatpay.error='.$wechatpay->getError());
        //{"appId":"wx3f408e2766a56176","timeStamp":1486470325,"nonceStr":"zWhOl9NsTDqKMCr4","package":"prepay_id=wx20170207202525cc264c65f60120364511","signType":"MD5","paySign":"944F01DA675D78CA0E673A2E3F9866F9"}
        if(isset($paysign) && !empty($paysign)){
            $paysign = json_decode($paysign, true);
            Log::record('paysign='.json_encode($paysign));
        }
        return view('mppay_tpl', ["trade"=>$trade, "jssign"=>$jssign, "paysign"=>$paysign]);
        /* for testting
        $trade_no = input('trade_no');
        $PayService = new PayService;
        $trade = $PayService->get($trade_no);

        return view('mppay_tpl', ["trade"=>$trade, "jssign"=>[], "paysign"=>[]]);
        */
    }

    public function notify(){
        if (Request::instance()->isGet()){
            $trade_no = "20170316225847148967";
            $transaction_id = "XXXXXXXXX";
            $PayService = new PayService;
            $PayService->updatePayStat($trade_no, 1, $transaction_id);
        }else{
            $wechatpay = new Wechatpay(array(
                    'appid'     => config('wx_appid'),
                    'secret'    => config('wx_appsecret'),
                    'pem'       => config('wxpay_pem'),
                    'mch_id'    => config('wxpay_mchid'),
                   'payKey'    => config('wxpay_paykey'),
            ));
            $notify = $wechatpay->getNotify();
            if($notify){
                Log::record(json_encode($notify));
                //{"appid":"wx8c4d0fba3ac9acbb","bank_type":"CFT","cash_fee":"1","fee_type":"CNY","is_subscribe":"Y","mch_id":"1404056502","nonce_str":"CbnYvNWIix01GKfB","openid":"o-GgvxADFF--8qp1h2tn1THD0Md0","out_trade_no":"20170320095700148997","result_code":"SUCCESS","return_code":"SUCCESS","sign":"7494E79C4061A3B1587DE8714CC85699","time_end":"20170320095719","total_fee":"1","trade_type":"JSAPI","transaction_id":"4006242001201703203994951520"}
                $PayService = new PayService;
                $PayService->addNotify(json_encode($notify));
                $return_code    = $notify['return_code'];
                $result_code    = $notify['result_code'];
                $trade_no       = $notify['out_trade_no'];
                $openid         = $notify['openid'];
                $transaction_id = $notify['transaction_id'];
                if(strcasecmp('SUCCESS', $return_code) == 0
                    && strcasecmp('SUCCESS', $result_code) == 0){
                    $PayService->updatePayStat($trade_no, 1, $transaction_id);
                    $wxmsg_notifyurl = "http://".config('app_host')."/index/index/newBookNotify?trade_no=$trade_no";
                    $jsonstr = http_get($wxmsg_notifyurl);
                    Log::record($wxmsg_notifyurl);
                    Log::record($jsonstr);
                }
            }
        }
        
        return xml(
            $data = ['return_code'=>'<![CDATA[SUCCESS]]>', 'return_msg'=>'<![CDATA[OK]]>'],
            $code = 200, 
            $header = [],
            $options = ['root_node' => 'xml']
        );
    }

    //需要使用谷歌API生成二维码
    //https://chart.googleapis.com/chart?cht=qr&chs=200x200&choe=UTF-8&chld=L|4&chl=http://pay.xifenhezi.com
    public function webpay()
    {
        $oid = input('get.oid');

        $PayService = new PayService;
        $order = $PayService->get($oid);
        /*
        $wechatpay = new Wechatpay(array(
                    'appid'     => Index::WX_APPID,
                    'secret'    => Index::WX_APPSECRET,
                    'pem'       => 'application',
                    'mch_id'    => Index::WXPAY_MCHID,
                    'payKey'    => Index::WXPAY_PAYKEY,
        ));
        $paysign = $wechatpay->webUnifiedOrder(
                    $product_id = strtotime(date("Y-m-d H:i:s")), 
                    $orderId = $orderid, 
                    $money = 0.01, 
                    $body = "平台订单", 
                    $notify_url = Index::WXPAY_NOTIFYURL, 
                    $extend = array());
        return view('webpay', ["paysign"=>$paysign]);
        */
        return view('webpay', ["order"=>$order, "paysign"=>array()]);
    }

    public function test(){
        $PayService = new PayService;
        $ret = ['t'=>$PayService->gen_trade_no()];
        return json($ret);
    }
}
