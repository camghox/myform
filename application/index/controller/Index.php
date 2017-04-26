<?php
namespace app\index\controller;

use think\Session;
use think\Log;

use app\pay\wechat\Wechat;
use app\pay\wechat\Wechatpay;

use app\index\service\UserService;
use app\index\service\RoomService;
use app\pay\service\PayService;

class Index
{
    /*
     * 检查是否微信授权用户
     */
    private function is_valid_wx_user(){
        $currurl = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        Session::set('red_url', $currurl);

        $open_id = Session::get('open_id');
        //if(config('app_debug')) return true;

        return isset($open_id) && !empty($open_id);
    }

    public function index()
    {
        return json(['ver'=>0.1, 'ret'=>0, 'msg'=>'德盛行邀你集福卡兑大奖']);
    }

    public function rooms()
    {
        if(!$this->is_valid_wx_user()){
            return redirect('/wechat');
        }

        $startdate = datestr_format('m/d l', time() + 1*24*3600);
        $enddate = datestr_format('m/d l', time() + 2*24*3600);

        $RoomService = new RoomService;
        $list = $RoomService->find();
        return view('rooms_tpl', ['list'=>$list, 'startdate'=>$startdate, 'enddate'=>$enddate, 'dates'=>1]);
    }
    
    public function room()
    {
        //Session::set('open_id', 'o-GgvxH8Wn4MHWq3-1fCdmvztKI0');
        if(!$this->is_valid_wx_user()){
            return redirect('/wechat');
        }
        
        $id = input('id');
        Session::set('room_id', $id);

        $startdate = datestr_format('m/d l', time() + 1*24*3600);
        $enddate = datestr_format('m/d l', time() + 2*24*3600);

        $RoomService = new RoomService;
        $room = $RoomService->get($id);
        return view('room_tpl', ['room'=>$room, 'startdate'=>$startdate, 'enddate'=>$enddate, 'dates'=>1]);
    }

    public function order(){
        $type = input("t");
        $trade = [];
        $trade_no = input('trade_no');
        if($trade_no){
            $PayService = new PayService;
            $trade = $PayService->get($trade_no);
        }
        if($type && strcasecmp($type, 'm') == 0){
            return view('order_m_tpl', ['trade'=>$trade]);
        }
        return view('order_tpl', ['trade'=>$trade]);
    }

    /* 
     * 确认预订 
     */
    public function prebook(){
        $trade = [];
        $indate = input('indate');
        $outdate = input('outdate');

        $open_id = Session::get('open_id');
        if(!isset($open_id) || empty($open_id)){
            return redirect('/wechat');
        }
        $room_id = Session::get('room_id');
        if(!isset($room_id) || empty($room_id)){
            return redirect("/rooms");
        }
        $RoomService = new RoomService;
        $room = $RoomService->get($room_id);
        return view('book_tpl', ['indate'=>$indate, 'outdate'=>$outdate, 'room'=>$room]);
    }
    public function book(){
        $indate     = input('post.indate');
        $outdate    = input('post.outdate');
        $phone      = input('post.phone');
        $contact    = input('post.contact');
        $room_id    = input('post.room_id');
        $open_id    = Session::get('open_id');

        $RoomService = new RoomService;
        $room = $RoomService->get($room_id);

        $PayService = new PayService;
        $trade_no = $PayService->add($open_id, $room['outid'].$room['title'], $room['price'], $indate, $outdate, $phone, $contact);
        //$trade_no = $PayService->add($content, $price, $indate, $outdate, $phone, $contact);

        if(!isset($trade_no) || empty($trade_no)){
            Session::set('trade_no', $trade_no);
            return redirect("/room?id=$room_id");
        }
        return redirect("/mppay?trade_no=$trade_no");
    }

    public function newBookNotify(){
        $wechat = new Wechat(array(
                'token'         =>  config('wx_token'), //填写你设定的key
                'encodingaeskey'=>  config('wx_encodingasekey'), //填写加密用的EncodingAESKey，如接口为明文模式可忽略
                'appid'         =>  config('wx_appid'), //填写高级调用功能的app id
                'appsecret'     =>  config('wx_appsecret'), //填写高级调用功能的密钥
                'agentid'       =>  '1', //应用的id
                'debug'         =>  true, //调试开关
                '_logcallback'=>'logg', //调试输出方法，需要有一个string类型的参数
        ));
        $trade = [];
        $trade_no = input('trade_no');
        if(!$trade_no){
            return false;
        }
        $PayService = new PayService;
        $trade = $PayService->get($trade_no);
        if(!$trade){
            return false;
        }
        //通知用户
        $wechat->sendTemplateMessage($data = [
            "touser"        =>$trade['wx_openid'],
            "template_id"   =>"TyKGEJ6_1CJw_RFELbTJ0O6a89hZXoDsL23ETxXFsKk",
            "url"           =>"http://".$_SERVER['HTTP_HOST']."/order?trade_no=".$trade['trade_no'],
            "topcolor"      =>"#FF0000",
            "data"          =>[
                "first"=>[
                    "value"=>"尊敬的用户您好，您的预订已经提交，工作人员2小时内审核，请留意。",
                    "color"=>"#173177"
                ],
                "OrderID"=>[
                    "value"=>$trade['trade_no'],
                    "color"=>"#173177"
                ],
                "PersonName"=>[
                    "value"=>$trade['contact'],
                    "color"=>"#173177"
                ],
                "CheckInDate"=>[
                    "value"=>$trade['indate'],
                    "color"=>"#173177"
                ],
                "CheckOutDate"=>[
                    "value"=>$trade['outdate'],
                    "color"=>"#173177"
                ],
                "remark"=>[
                    "value"=>"如要取消预订，请在入住前24小时内取消。如有问题请联系酒店客服。",
                    "color"=>"#173177"
                ]
            ]
        ]);
        
        $UserService = new UserService;
        $staffs = $UserService->findStaff();
        //通知管理员
        foreach($staffs as $staff){
            $wechat->sendTemplateMessage($data = [
                "touser"        =>$staff['wx_openid'],
                "template_id"   =>"Uihp447RqASEEgP5C5dSlAoZSKEwmUrxkVAxepGRKh4",
                "url"           =>"http://".$_SERVER['HTTP_HOST']."/order?trade_no=".$trade['trade_no'],
                "topcolor"      =>"#FF0000",
                "data"          =>[
                    "first"=>[
                        "value"=>"有新的预约订单！",
                        "color"=>"#173177"
                    ],
                    "orderdate"=>[
                        "value"=>$trade['addtime'],
                        "color"=>"#173177"
                    ],
                    "roomNum"=>[
                        "value"=>$trade['content'],
                        "color"=>"#173177"
                    ],
                    "date"=>[
                        "value"=>$trade['indate'].'/'.$trade['outdate'],
                        "color"=>"#173177"
                    ],
                    "customerName"=>[
                        "value"=>$trade['contact'].'/'.$trade['phone'],
                        "color"=>"#173177"
                    ],
                     "price"=>[
                        "value"=>$trade['price'],
                        "color"=>"#173177"
                    ],
                     "orderNo"=>[
                        "value"=>$trade['trade_no'],
                        "color"=>"#173177"
                    ],
                     "arrivalTime"=>[
                        "value"=>$trade['indate'],
                        "color"=>"#173177"
                    ],
                    "remark"=>[
                        "value"=>"",
                        "color"=>"#173177"
                    ]
                ]
            ]);
        }
        return json($wechat);
    }

    public function cancelBookNotify(){
        $wechat = new Wechat(array(
                'token'         =>  config('wx_token'), //填写你设定的key
                'encodingaeskey'=>  config('wx_encodingasekey'), //填写加密用的EncodingAESKey，如接口为明文模式可忽略
                'appid'         =>  config('wx_appid'), //填写高级调用功能的app id
                'appsecret'     =>  config('wx_appsecret'), //填写高级调用功能的密钥
                'agentid'       =>  '1', //应用的id
                'debug'         =>  true, //调试开关
                '_logcallback'=>'logg', //调试输出方法，需要有一个string类型的参数
        ));
        $trade = [];
        $trade_no = input('trade_no');
        if(!$trade_no){
            return false;
        }
        $PayService = new PayService;
        $trade = $PayService->get($trade_no);
        if(!$trade){
            return false;
        }
        //通知用户
        $wechat->sendTemplateMessage($data = [
            "touser"        =>$trade['wx_openid'],
            "template_id"   =>"NrF-2BCeKMuo8WCtadSmPcu0yzGaNpohEurlr0CH5FM",
            "url"           =>"http://".$_SERVER['HTTP_HOST']."/order?trade_no=".$trade['trade_no'],
            "topcolor"      =>"#FF0000",
            "data"          =>[
                "first"=>[
                    "value"=>"尊敬的用户您好，经确认您未入住客房，现将订单取消。",
                    "color"=>"#173177"
                ],
                "keyword1"=>[
                    "value"=>'Loveme情侣主题酒店',
                    "color"=>"#173177"
                ],
                "keyword2"=>[
                    "value"=>$trade['content'],
                    "color"=>"#173177"
                ],
                "keyword3"=>[
                    "value"=>$trade['price'].'元',
                    "color"=>"#173177"
                ],
                "keyword4"=>[
                    "value"=>$trade['indate'].'/'.$trade['outdate'],
                    "color"=>"#173177"
                ],
                "keyword5"=>[
                    "value"=>$trade['trade_no'],
                    "color"=>"#173177"
                ],
                "remark"=>[
                    "value"=>"如有问题请联系酒店。",
                    "color"=>"#173177"
                ]
            ]
        ]);
        $UserService = new UserService;
        $staffs = $UserService->findStaff();
        //通知管理员
        foreach($staffs as $staff){
            $wechat->sendTemplateMessage($data = [
                "touser"        =>$staff['wx_openid'],
                "template_id"   =>"wVk67pAK-XhXXsfRC1n6EgDS9eltmhlopfhijj21ypE",
                "url"           =>"http://".$_SERVER['HTTP_HOST']."/order?trade_no=".$trade['trade_no'],
                "topcolor"      =>"#FF0000",
                "data"          =>[
                    "first"=>[
                        "value"=>"客户已经取消订单。",
                        "color"=>"#173177"
                    ],
                    "keyword1"=>[
                        "value"=>$trade['trade_no'],
                        "color"=>"#173177"
                    ],
                    "keyword2"=>[
                        "value"=>$trade['contact'].'/'.$trade['phone'],
                        "color"=>"#173177"
                    ],
                    "keyword3"=>[
                        "value"=>$trade['indate'].'/'.$trade['outdate'],
                        "color"=>"#173177"
                    ],
                    "keyword4"=>[
                        "value"=>$trade['content'],
                        "color"=>"#173177"
                    ],
                     "keyword5"=>[
                        "value"=>$trade['price'],
                        "color"=>"#173177"
                    ],
                    "remark"=>[
                        "value"=>"",
                        "color"=>"#173177"
                    ]
                ]
            ]);
        }
        return json($wechat);
    }
    public function staffs(){
        $UserService = new UserService;
        $list = $UserService->findStaff();
        return json(['list'=>1]);
    }
    public function manage()
    {
        $RoomService = new RoomService;
        $list = $RoomService->find();
        return view('manage_tpl', ['list'=>$list]);
    }
    public function test(){
        //$htmlstr = http_get("http://".$_SERVER['HTTP_HOST']."/index/index/newBookNotify?trade_no=20170316225847148967");
        $htmlstr = http_get("http://www.baidu.com");
        return json([
            "ret"=>0,
            "msg"=>"test",
            "html"=>$htmlstr
        ]);
    }
}
