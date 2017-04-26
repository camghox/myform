<?php
namespace app\index\service;

use think\Log;
use think\Db;

use app\index\model\RoomBase;
use app\index\model\OrderRoom;

class OrderBase
{
    public function add($uid, $rbid){
        $RoomBase = new RoomBase;
        $room = $RoomBase->get($rbid);
        $room['uid'] = $uid;
        $room['addtime'] = date('Y-m-d');

        $OrderRoom = new OrderRoom;
        $OrderRoom->save($room);
        return $room;
    }
}
