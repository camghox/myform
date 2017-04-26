<?php
namespace app\index\controller;

use think\Session;
use think\Log;

use app\index\service\RoomService;
use app\pay\service\PayService;

class Ajax
{
    public function order_stat(){
        $trade_no  = input('trade_no');
        $stat      = input('stat');

        if($trade_no && $stat){
            $PayService = new PayService;
            $trade = $PayService->get($trade_no);
            $trade['tradestat'] = stat;
            $trade = $trade->save();

            $RoomService = new RoomService;
        }
        return json(['ret'=>0, 'msg'=>'']);
    }
    public function room_stat()
    {
        $rbid  = input('rbid');
        $stat = input('stat');
        $msg = '';
        if($rbid && $stat){
            $RoomService = new RoomService;
            $room = $RoomService->get($rbid);
            if($room){
                $room['stat'] = $stat;
                $room->save();
                $msg = $room;
            }
        }
        return json(['ret'=>0, 'msg'=>$msg]);
    }
    public function room_stat_en(){
        $RoomService = new RoomService;
        $RoomService->setAllEnabled();
        return json(['ret'=>0, 'msg'=>'']);
    }
    public function room_price()
    {
        $rbid  = input('rbid');
        $price = input('price');
        $msg = '';
        if($rbid && $price){
            $RoomService = new RoomService;
            $room = $RoomService->get($rbid);
            if($room){
                $room['price'] = $price;
                $room->save();
                $msg = $room;
            }
        }
        return json(['ret'=>0, 'msg'=>$msg]);
    }
}
