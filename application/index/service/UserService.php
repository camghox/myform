<?php
namespace app\index\service;

use think\Log;
use think\Db;

use app\index\model\UserBase;

class UserService
{
    public function findStaff(){
        $UserBase = model('UserBase');
        $list = $UserBase->where(["is_staff"=>1])->select();
        return $list;
    }
    public function addOrUpdate($open_id, $user){
        if(!$open_id || !$user) return false;
        $UserBase = model('app\index\model\UserBase');
        $userold = $UserBase->where("wx_openid='$open_id'")->find();
        if($userold){
            $userold->nickname = $user['nickname'];
            $userold->sex      = $user['sex'];
            $userold->city     = $user['city'];
            $userold->avatar   = $user['avatar'];
            $userold->up_time  = date('Y-m-d H:i:s');
            $userold->save();
        }else{
            $user['create_time'] = date('Y-m-d H:i:s');
            $user['status'] = 1;
            $UserBase->save($user);
        }
        return $user;
    }

    public function newpass($uid, $oldpasswd, $newpasswd){
        $user = UserBase::get($uid);
        $oldpasswd = $this->passwd($oldpasswd);
        $newpasswd = $this->passwd($newpasswd);
        if(isset($user) && strcasecmp($user['passwd'], $oldpasswd) == 0){
            $user['passwd']     = $newpasswd;
            $user['up_time']    = date('Y-m-d H:i:s');
            $user->save();
        }
    }

    private function passwd($passwd){
        if(isset($passwd))
            return md5(md5($passwd).$this->primary_key);
        else
            return '';
    }
}
