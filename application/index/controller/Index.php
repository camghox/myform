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
        return isset($open_id) && !empty($open_id);
    }

    public function index()
    {
        return json(['ver'=>0.1, 'ret'=>0, 'msg'=>'我的表单']);
    }

    public function form(){
        return view('biaodan2', []);
    }

    public function submit(){
        $data = I('post.');
        \think\Log::record("data=$data");
        return view('biaodan', []);
    }

    /*
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
    }*/

}
