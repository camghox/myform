<?php
namespace app\index\service;

use think\Log;
use think\Db;

use app\index\model\RoomBase;

class RoomService
{
    //private $primary_key = "72fanke@peizheng";

    public function get($id){
        $RoomBase = model('RoomBase');
        $room = $RoomBase->where("stat > 0 AND (rbid=:rbid OR outid=:outid OR title=:title)")
                 ->bind(["rbid"=>$id, "outid"=>$id, "title"=>$id])
                 ->find();
        return $room;
    }
    public function save($room){
        $RoomBase = model('RoomBase');
        return $RoomBase->save($room);
    }
    public function setAllEnabled(){
        $RoomBase = model('RoomBase');
        return $RoomBase->where("stat > 0")->update(["stat"=>1]);
    }
    public function find($pn = 1, $ps = 30){
        $RoomBase = model('RoomBase');
        $list = $RoomBase->where("stat > 0")
                 ->page("$pn,$ps")
                 ->order(['outid'=>'asc'])
                 ->select();
        return $list;
    }    
}
