<?php
namespace app\index\service;

use think\Log;
use think\Db;

use app\index\model\SoccerMyform;

class MyformService
{
    public function add($name,$mobile,$child_name,$child_age){
        $SoccerMyform = model('SoccerMyform');
        $count = $SoccerMyform->where(['mobile'=>$mobile])->count();
        if($count < 1){
            $user = array(
                'name'      => $name,
                'mobile'        =>$mobile,
                'child_name'    =>$child_name,
                'child_age'     =>$child_age,
                'create_time'   => date('Y-m-d H:i:s')
            );
            $SoccerMyform->save($user);
            return true;
        }
        return false;
    }

/**
    * 验证手机号是否正确
    * @author honfei
    * @param number $mobile
*/
    function isMobile($mobile) {
        if (!is_numeric($mobile)) {
            return false;
        }
        return preg_match('#^13[\d]{9}$|^14[5,7]{1}\d{8}$|^15[^4]{1}\d{8}$|^17[0,6,7,8]{1}\d{8}$|^18[\d]{9}$#', $mobile) ? true : false;
    }
}